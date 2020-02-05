<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class RegistrationPresenter extends _BasePresenter
{
  public function renderConfirmation($account)
  {
    $user = $this->db->table('users')
      ->where('email', \base64_decode($account))
      ->fetch();

    $error = '';

    if (empty($user)) {
      $error = 'not-found';
    }
    elseif ($user->enabled) {
      $error = 'already-enabled';
    }
    else {
      $this->db->table('users')
        ->where('id', $user->id)
        ->update(['enabled' => true]);
    }

    $this->template->result = empty($error);
    $this->template->error = $error;
  }
}
