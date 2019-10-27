<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use App;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected $db;
    protected $utils;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->utils = new App\Utils($database);
    }

    protected function startup()
    {
        parent::startup();

        if (!$this->getSession("admin")->offsetGet("logged")) {
            $this->redirect(":Registration:signIn");

            return;
        }
    }
}
