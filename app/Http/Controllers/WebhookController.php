<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Services\Deploy;
use App\Models\Log;
use Illuminate\Support\Facades\Log as laravelLog;

class WebhookController extends Controller
{
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {

  }

  public function deploy($rep_name = false)
  {
    laravelLog::info('starting');
    $deploy = new Deploy($rep_name);
    $deploy->execute();
    $log = new Log;
    $log->content = $deploy->getLog();
    $log->repo_id = $deploy->getRepo()->id;
    if ($rep_name) {
      $log->madeBy = 'user';
      $log->user_id = Auth::user()->id;
    }else{
      $log->madeBy = 'webhook';
      $log->user_id = 0;
    }
    $log->save();
    if ($rep_name) {
      return redirect()->back();
    }else{
      return $log;
    }
  }

}
