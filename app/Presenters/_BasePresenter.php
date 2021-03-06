<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Components;

abstract class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected $db;
    protected $dbWrapper;
    protected $emailWrapper;
    protected $statsWrapper;
    protected $utils;
    protected $formComponent;

    protected $section_admin;
    protected $section_frontend;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->dbWrapper = new \App\Utils\DbWrapper($this);
        $this->emailWrapper = new \App\Utils\EmailWrapper($this);
        $this->statsWrapper = new \App\Utils\StatsWrapper($this);
        $this->utils = new \App\Utils($this);
        $this->formComponent = new Components\FormComponent($this);
    }

    public function startup()
    {
        parent::startup();

        $this->section_admin = $this->getSession('admin');
        $this->section_frontend = $this->getSession('frontend');

        $this->template->facebookShare = $this->_getFacebookShare();

        $this->template->logged = !empty($this->section_admin->user);
        $this->template->user = $this->dbWrapper->getUserById($this->section_admin->user_id);

        $this->template->topBrands = $this->dbWrapper->getTopCarMake(6, 4);
        $this->template->userWishlist = $this->dbWrapper->getUserWishlist($this->section_admin->user_id);
    }

    protected function _getFacebookShare()
    {
        $facebookShare = new \StdClass();

        $facebookShare->url = \str_replace(':80', '', $this->getHttpRequest()->getUrl());
        $facebookShare->title = 'AutoIdeale.it';
        $facebookShare->description = 'Acquista la tua auto';
        $facebookShare->image = 'https://www.autoideale.it/img/gallery/slide1.jpg';

        return $facebookShare;
    }

    public function getDbService(): \Nette\Database\Context
    {
        return $this->db;
    }

    public function getConfig()
    {
        return $this->context->getParameters();
    }

    public function getUtils(): \App\Utils
    {
        return $this->utils;
    }

    public function handleLoadCarModels($formName, $makeId)
    {
        $this->formComponent->handleLoadCarModels($formName, $makeId);
    }

    public function handleAddToWishlist($postId, $add)
    {
      if ($add === "true") {
        $this->dbWrapper->addPostToWishlist($postId, $this->section_admin->user_id);
        $this->statsWrapper->addWishlist($postId);

        $post = $this->dbWrapper->getPost($postId);
        $this->emailWrapper->sendAddWishlist($post);
      }
      else {
        $this->dbWrapper->removePostFromWishlist($postId, $this->section_admin->user_id);
      }

      $this->template->userWishlist = $this->dbWrapper->getUserWishlist($this->section_admin->user_id);

      if ($this->isLinkCurrent('Wishlist:index')) {
        $this->redrawControl('wishlist');
      }
      elseif ($this->isLinkCurrent('Listing:detail')) {
        $this->redrawControl('asideWrapper');
        $this->redrawControl('wishlistButton');
      }
      else {
        $this->sendJson([
          'success' => true
        ]);
      }
    }
}
