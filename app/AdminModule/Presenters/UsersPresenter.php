<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use \Nette\Application\Responses\JsonResponse;
use App\Utils;

final class UsersPresenter extends _BasePresenter
{
    public function renderIndex(): void
    {
        $this->template->users = $this->db->table('users')->fetchAll();
    }

    public function renderDetail($userId): void
    {
        $this->template->user = $this->db->table('users')->get($userId);
        $this->template->tempPath = \Nette\Utils\Random::generate(10);
    }

    protected function createComponentUserDetailForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addHidden('userId')
             ->setDefaultValue($this->template->user->id ?? "");

        $form->addHidden('tempPath')
            ->setDefaultValue($this->template->tempPath ?? "");

       $form->addUpload('image', 'Foto profilo')
            ->addRule(UI\Form::IMAGE, 'La foto deve essere JPEG, PNG or GIF');

        $form->addText('name', 'Nome')
             ->setRequired('Inserisci il tuo nome')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->name ?? "");

        $form->addText('surname', 'Cognome')
             ->setRequired('Inserisci il tuo cognome')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->surname ?? "");

        $form->addText('address', 'Indirizzo')
             ->setRequired('Inserisci il tuo indirizzo')
             ->setHtmlAttribute('placeholder', 'Via e numero civico')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->address ?? "");

        $form->addText('city', 'Città')
             ->setRequired('Inserisci la tua città')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->city ?? "");

        $form->addText('cap', 'Cap')
             ->setRequired('Inserisci il tuo CAP')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->cap ?? "");

        $form->addText('telephone', 'Telefono')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->telephone ?? "");

        $form->addText('mobile', 'Cellulare')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->user->mobile ?? "");

         $form->addTextArea('info', 'Informazioni personali')
              ->setHtmlAttribute('class', 'form-control')
              ->setHtmlAttribute('placeholder', 'Scrivi qualcosa su di te')
              ->setDefaultValue($this->template->user->info ?? "");

         $form->addPassword('new_password', 'Nuova password')
              ->setHtmlAttribute('class', 'form-control');

         $form->addPassword('confirm_password', 'Conferma la nuova password')
              ->setHtmlAttribute('class', 'form-control');

         $form->addEmail('new_email', 'Nuova email')
              ->setHtmlAttribute('class', 'form-control');

         $form->addEmail('confirm_email', 'Conferma la nuova email')
              ->setHtmlAttribute('class', 'form-control');

         $form->addSubmit('save', 'Salva');

         if (!empty($this->template->user->id)) {
             $form->onSubmit[] = [$this, 'submitEditUser'];
         }
         else {
             $form->onSubmit[] = [$this, 'submitAddUser'];
         }

         return $form;
    }

    public function handleDeleteImage($imageId)
    {
         $this->filesWrapper->deleteUserImage($imageId);

         $this->sendResponse(new JsonResponse(['result' => true]));
    }

    public function submitEditUser(UI\Form $form): void
    {
        $values = $form->getValues();
    }

    public function submitAddUser(UI\Form $form): void
    {
        $values = $form->getValues();
    }
}
