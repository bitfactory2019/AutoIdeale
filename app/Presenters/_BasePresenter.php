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

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->dbWrapper = new \App\Utils\DbWrapper($this);
        $this->emailWrapper = new \App\Utils\EmailWrapper($this);
        $this->utils = new \App\Utils($this);
        $this->formComponent = new Components\FormComponent($this);
    }

    public function getDbService(): \Nette\Database\Context
    {
        return $this->db;
    }

    public function getUtils(): \App\Utils
    {
        return $this->utils;
    }

    protected function createComponentSignInForm()
    {
        $form = new \Nette\Application\UI\Form();

        $form->addEmail('email', 'Email')->setRequired('Inserisci l\'email');
        $form->addPassword('password', 'Password')->setRequired('Inserisci la password');
        $form->addSubmit('signin', 'Login');

        $form->onSuccess[] = function() use ($form) {
            $values = $form->getValues();

            $user = $this->dbWrapper->getUserLogin($values);

            if (empty($user)) {
                $this->flashMessage("Utente non riconosciuto", "danger");
            }
            else {
                $this->flashMessage("Bentornato {$user->name} {$user->surname}", "success");

                $this->getSession("admin")->offsetSet("logged", true);
                $this->getSession("admin")->offsetSet("user", $user->toArray());
                $this->getSession("admin")->offsetSet("remember", $values->remember ?? false);

                $this->dbWrapper->saveUserLogin($user->id);

                $this->redirect('Admin:Dashboard:index');
            }
        };

        return $form;
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
