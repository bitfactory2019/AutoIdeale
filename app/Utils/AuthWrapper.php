<?php

namespace App\Utils;

use Nette\Diagnostic\Debugger;
use Nette\Utils\DateTime;

class AuthWrapper
{
  private $presenter;
  private $db;
  private $utils;

  public function __construct(\Nette\Application\UI\Presenter $presenter)
  {
    $this->presenter = $presenter;
    $this->db = $this->presenter->getDbService();
    $this->utils = new \App\Utils($this->presenter);
  }

  private function _getUser()
  {
    return $this->db->table('users')
      ->get($this->presenter->getSession("admin")->user_id);
  }

  public function isPrivate()
  {
    return $this->_getUser()->groups->name === 'private';
  }

  public function isCompany()
  {
    return $this->_getUser()->groups->name === 'company';
  }

  public function isAdmin()
  {
    return $this->_getUser()->groups->name === 'admin';
  }
}
