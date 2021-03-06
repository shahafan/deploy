<?php

namespace App\Services;

use App\Models\Repo;

class Deploy
{

    /**
     * A callback function to call after the deploy has finished.
     *
     * @var callback
     */
    public $post_deploy;

    private $_payload;

    private $_repository;

    private $_log = [];

    private $_dir = '/home';

    /**
     * Sets up defaults.
     *
     * @param bool $rep_id
     * @internal param string $directory Directory where your website is located
     * @internal param array $data Information about the deployment
     */
    public function __construct($rep_id = false)
    {
        $this->log('**** Attempting deployment... ****');

        if ($rep_id)
        {
            // the repo form the db
            $this->_repository = Repo::findOrFail($rep_id);
        } else
        { // the called made by webhook
            $this->initPayload();
            $rep_name = $this->_payload->repository->full_name;
            // the repo form the db
            $this->_repository = Repo::where('bitbucket', $rep_name)->first();
            if (!$this->_repository->auto_deploy)
            {
                die();
            }
        }
        $this->_repository->touch();
        $this->_dir .= '/'.$this->_repository->account.'/'.$this->_repository->directory;
    }

    public function log($value)
    {
        $this->_log[] = $value;
    }

    public function initPayload()
    {
        if (isset(
            $_SERVER['HTTP_X_EVENT_KEY'], $_SERVER['HTTP_X_HOOK_UUID'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']
        ))
        {
            $this->log(
                '*** '.$_SERVER['HTTP_X_EVENT_KEY'].' #'.$_SERVER['HTTP_X_HOOK_UUID'].' ('.$_SERVER['HTTP_USER_AGENT'].')'
            );
            $this->log('remote addr: '.$_SERVER['REMOTE_ADDR']);
        } else
        {
            $this->log('*** [unknown http event key] #[unknown http hook uuid] (unknown http user agent)');
        }

        $this->_payload = json_decode(file_get_contents('php://input'));

        if (empty($this->_payload))
        {
            $this->log("No payload data for checkout!");
            exit;
        }

        if (!isset($this->_payload->repository->full_name))
        {
            $this->log("Invalid payload data was received!");
            exit;
        }

        $this->log("Valid payload was received");
    }

    /**
     * Executes the necessary commands to deploy the website.
     * to allow it to run as sudo:
     * Cmnd_Alias DEPLOY_COMMANDS = /usr/bin/git reset, /usr/bin/git pull, /usr/bin/chmod, /usr/bin/chown
     * user ALL=NOPASSWD: DEPLOY_COMMANDS
     */
    public function execute()
    {
        $this->log('executing on '.$this->_repository->bitbucket);
        // Make sure we're in the right directory
        chdir($this->_dir);
        $this->log('Changing working directory to '.$this->_dir);
        // Discard any changes to tracked files since our last deploy
        exec('sudo git reset --hard HEAD', $output);
        $this->log('Reseting repository... ');
        $this->log($output);
        $output = '';

        // Update the local repository
        exec('sudo git pull '.$this->_repository->remote.' '.$this->_repository->branch, $output);
        $this->log('Pulling in changes... ');
        $this->log($output);
        $output = '';

        // changing permissions
        exec('sudo chown -R '.$this->_repository->account.':'.$this->_repository->account.' '.$this->_dir, $output);
        $this->log('changing permissions... ');
        $this->log($output);
        $output = '';

        // Secure the .git directory
        exec('sudo chmod -R og-rx .git', $output);
        $this->log('Securing .git directory... ');
        $this->log($output);
        $output = '';

        if ($this->_repository->post_deploy)
        {
            // running post deploy scripts
            exec($this->_repository->post_deploy, $output);
            $this->log('Post deploy scripts: '.$this->_repository->post_deploy);
            $this->log($output);
            $output = '';
        }

        $this->log('**** Deployment successful. ****');
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * @return mixed
     */
    public function getRepo()
    {
        return $this->_repository;
    }

}
