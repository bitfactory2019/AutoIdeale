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
    protected $utils;
    protected $formComponent;

    protected $section_admin;
    protected $section_frontend;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->dbWrapper = new \App\Utils\DbWrapper($this);
        $this->emailWrapper = new \App\Utils\EmailWrapper($this);
        $this->utils = new \App\Utils($this);
        $this->formComponent = new Components\FormComponent($this);
    }

    public function startup()
    {
        parent::startup();

        $this->section_admin = $this->getSession('admin');
        $this->section_frontend = $this->getSession('frontend');

        $this->template->logged = !empty($this->section_admin->user);
        $this->template->user = $this->dbWrapper->getUserById($this->section_admin->user_id);
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

    public function handleLoadBrandModels($formName, $brandId)
    {
        $this->formComponent->handleLoadBrandModels($formName, $brandId);
    }

    public function handleLoadModelTypes($formName, $modelId)
    {
        $this->formComponent->handleLoadModelTypes($formName, $modelId);
    }

    public function handleLoadTypeYears($formName, $typeId)
    {
        $this->formComponent->handleLoadTypeYears($formName, $typeId);
    }

    public function handleLoadTypeYearMonths($formName, $typeId, $year)
    {
        $this->formComponent->handleLoadTypeYearMonths($formName, $typeId, $year);
    }
}
