<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class AccountPresenter extends _BasePresenter
{
    protected function createComponentSignInForm()
    {
        $form = new Form();

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

                $section = $this->getSession("admin");

                $section->remove();
                $section->user_id = $user->id;
                $section->remember = $values->remember ?? false;

                $this->dbWrapper->saveUserLogin($user->id);

                $this->redirect('Admin:Dashboard:'.$user->groups->admin_index);
            }
        };

        return $form;
    }

    protected function createComponentRecoveryPasswordForm()
    {
        $form = new Form();

        $form->addEmail('email', 'Email')->setRequired('Inserisci l\'email');
        $form->addSubmit('recovery', 'Reset Password');

        $form->onSuccess[] = function() use ($form) {
        };

        return $form;
    }

    protected function createComponentSignUpForm()
    {
        $form = new Form();

        $form->addEmail('email', 'Email')->setRequired();
        $form->addPassword('password', 'Password')->setRequired();

        $form->addRadioList('client_type')
             ->setItems([
               'private' => 'Privato',
               'company' => 'Azienda'
             ])
             ->setDefaultValue('private');

        $form->addText('name', 'Nome')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'private')
             ->setRequired();

        $form->addText('surname', 'Cognome')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'private')
             ->setRequired();

        $form->addText('company_name', 'Ragione Sociale')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'company')
             ->setRequired();

        $form->addText('unique_code', 'Codice Univoco')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'company')
             ->setRequired();

        $form->addText('email_pec', 'Indirizzo e-mail PEC')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'company')
             ->setRequired();

        $form->addText('iban', 'Codice IBAN')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'company')
             ->setRequired();

        $form->addText('address', 'Indirizzo')->setRequired();
        $form->addText('city', 'Città')->setRequired();
        $form->addText('cap', 'Codice postale')->setRequired();

        $form->addSelect('country', 'Paese')
             ->setItems(['Europa', 'Stati Uniti', 'Asia'], false);

        $form->addText('telephone', 'Telefono')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'private')
             ->setRequired();
        $form->addText('mobile', 'Cellulare')
             ->addConditionOn($form['client_type'], Form::EQUAL, 'private')
             ->setRequired();

        $form->addSubmit('signUp', 'Registrati');

        $form->onSuccess[] = function() use ($form) {
            $values = $form->getValues();

            $user = $this->dbWrapper->getUserByEmail($values->email);

            if ($user) {
                $this->flashMessage("L'email inserita è già registrata. Prova il recupero dei dati di accesso.", "danger");
            }
            else {
                $userId = $this->dbWrapper->addUser($values);

                if ($userId === false) {
                    $this->flashMessage("Si è verificato un errore con la registrazione, contatta l'assistenza.", "danger");
                }
                else {
                    $this->emailWrapper->sendNewUserConfirmation($values);

                    $this->flashMessage("La registrazione è avvenuta con successo!", "success");
                    $this->flashMessage("Un amministratore attiverà il tuo account appena possibile.", "success");

                    //$this->redirect("Registration:signIn");
                }
            }
        };

      return $form;
    }

    public function renderSignOut()
    {
        $section = $this->getSession("admin");

        foreach ($section as $key => $val) {
        	 unset($section->$key);
        }

        $this->redirect("Homepage:index");
    }
}
