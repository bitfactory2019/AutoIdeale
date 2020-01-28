<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use \Nette\Application\Responses\JsonResponse;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected $db;
    protected $authWrapper;
    protected $dbWrapper;
    protected $filesWrapper;
    protected $utils;

    protected $section;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->authWrapper = new \App\Utils\AuthWrapper($this);
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

        $this->section = $this->getSession("admin");

        if (empty($this->section->user_id)) {
            $this->redirect(":Account:index");
        }

        $this->template->user = $this->dbWrapper->getUserById($this->section->user_id);
        $this->template->posts = $this->dbWrapper->getPosts($this->section->user_id);
        $this->template->messages = $this->dbWrapper->getMessages($this->section->user_id);
        $this->template->newMessages = $this->dbWrapper->getMessages($this->section->user_id, true);
        $this->template->requests = $this->dbWrapper->getRequests($this->section->user_id);
        $this->template->pendingRequests = $this->dbWrapper->getRequests($this->section->user_id, 'pending');

        $this->template->isAdmin = $this->authWrapper->isAdmin();

        if ($this->authWrapper->isAdmin()) {
          $administrator = new \StdClass();
          $administrator->usersNo = $this->db->table('users')->count('*');
          $administrator->postsNo = $this->db->table('posts')->count('*');

          $administrator->newUsers = $this->db->table('users')
            ->where('enabled = 1')
            ->where('creation_time > ?', $this->template->user->last_login)
            ->count('*');

          $administrator->newPosts = $this->db->table('posts')
            ->where('approved = 1')
            ->where('creation_time > ?', $this->template->user->last_login)
            ->count('*');

          $this->template->administrator = $administrator;
        }
    }

    public function getConfig()
    {
        return $this->context->getParameters();
    }


    public function handleAddTempImages()
    {
        $images = $this->getHttpRequest()->getFile('images');

        $postFiles = $this->filesWrapper->uploadTempFiles(
             $this->getHttpRequest()->getQuery('tempPath'),
             $images
        );

        $this->sendResponse(new JsonResponse($postFiles));
    }

    public function handleDeleteTempImage($imageName)
    {
         $this->filesWrapper->deleteTempImage($imageName);

         $this->sendResponse(new JsonResponse(['result' => true]));
    }
}
