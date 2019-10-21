<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->getSession("admin")->offsetGet("logged")) {
            $this->redirect(":Registration:signIn");

            return;
        }
    }
}
