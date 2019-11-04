<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class RegistrationPresenter extends _BasePresenter
{
    protected function createComponentSignUpForm()
    {
        $form = new \Nette\Application\UI\Form();

        $form->addText('name', 'Nome')->setRequired('Inserisci il nome');
        $form->addText('surname', 'Cogome')->setRequired('Inserisci il cognome');
        $form->addEmail('email', 'Email')->setRequired('Inserisci l\'email');

        $passwordInput = $form->addPassword('password', 'Password')
            ->setRequired('Inserisci la password')
            ->addRule($form::PATTERN, "La password deve contenere maiuscole, minuscole e numeri", '[a-zA-Z0-9]{8,}');
        $form->addPassword('password2', 'Conferma password')
            ->setRequired('Inserisci la password di conferma')
            ->addRule($form::EQUAL, 'Verifica non riuscita, le password non coincidono', $passwordInput);

        $form->addSubmit('signup', 'Registrati');

        $form->onSuccess[] = function() use ($form) {
            $values = $form->getValues();

            $user = $this->dbWrapper->getUserByEmail($values->email);

            if ($user) {
                $this->flashMessage("L'email inserita è già registrata.", "danger");
                $this->flashMessage("Prova il recupero dei dati di accesso.", "danger");
            }
            else {
                $this->dbWrapper->addUser($values);
                $this->emailWrapper->sendNewUser($values);

                $this->flashMessage("La registrazione è avvenuta con successo!", "success");
                $this->flashMessage("Un amministratore attiverà il tuo account appena possibile.", "success");

                $this->redirect("Registration:signIn");
            }
        };

        return $form;
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
}