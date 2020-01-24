<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class WishlistPresenter extends _BasePresenter
{
  public function startup()
  {
      parent::startup();

      if (empty($this->section_admin->user_id)) {
        $this->redirect('Account:index');
      }
  }
}
