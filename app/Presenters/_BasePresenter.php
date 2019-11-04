<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected $db;
    protected $dbWrapper;
    protected $emailWrapper;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->dbWrapper = new \App\Utils\DbWrapper($this);
        $this->emailWrapper = new \App\Utils\EmailWrapper($this);
    }

    public function getDbService(): \Nette\Database\Context
    {
        return $this->db;
    }
}
