<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected $db;
    protected $dbWrapper;
    protected $filesWrapper;
    protected $utils;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->dbWrapper = new \App\Utils\DbWrapper($this);
        $this->filesWrapper = new \App\Utils\FilesWrapper($this);
        $this->utils = new \App\Utils($this);
    }

    public function getDbService(): \Nette\Database\Context
    {
        return $this->db;
    }

    protected function startup()
    {
        parent::startup();

        if (!$this->getSession("admin")->offsetGet("logged")) {
            $this->redirect(":Registration:signIn");

            return;
        }

        $this->template->user = $this->getAdminUser();
        $this->template->posts = $this->dbWrapper->getPosts($this->template->user['id']);
        $this->template->messages = $this->dbWrapper->getMessages($this->template->user['id']);
        $this->template->newMessages = $this->dbWrapper->getMessages($this->template->user['id'], true);
        $this->template->requests = $this->dbWrapper->getRequests($this->template->user['id']);
        $this->template->pendingRequests = $this->dbWrapper->getRequests($this->template->user['id'], 'pending');
    }

    protected function getAdminUser()
    {
        return $this->getSession('admin')->offsetGet('user');
    }

    public function getConfig()
    {
        return $this->context->getParameters();
    }
}
