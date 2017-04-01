<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class RepoForm extends Form
{
  public function buildForm()
  {
    $this
    ->add('url', 'text', [
      'label' => 'Url',
      'attr' => ['placeholder' => 'http://deploy.b2csandbox.com'],
      'rules' => 'required',
    ])
    ->add('bitbucket', 'text', [
      'label' => 'Bitbucket path',
      'attr' => ['placeholder' => 'b2cprint/deploy'],
      'rules' => 'required',
    ])
    ->add('directory', 'text', [
      'label' => 'Directory',
      'attr' => ['placeholder' => '/home/b2csandbox/deploy'],
      'rules' => 'required',
    ])
    ->add('remote', 'text', [
      'label' => 'Remote',
      'attr' => ['placeholder' => 'origin'],
      'rules' => 'required',
    ])
    ->add('branch', 'text', [
      'label' => 'Branch',
      'attr' => ['placeholder' => 'master'],
      'rules' => 'required',
    ])
    ->add('auto_deploy', 'checkbox', [
      'label' => 'Auto Deploy',
      'value' => 1,
      'checked' => true
    ])
    ->add('comments', 'textarea', [
      'label' => 'Comments',
      'attr' => ['rows' => 3, 'placeholder' => 'Anything else..'],
    ])
    ->add('submit', 'submit', [
      'label' => 'Save',
      'attr' => ['class' => 'btn btn-primary btn-block',],
    ])
    ;

  }
}
