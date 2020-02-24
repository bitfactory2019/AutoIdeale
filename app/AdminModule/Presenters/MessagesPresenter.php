<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use \Nette\Application\Responses\JsonResponse;
use App\Utils;

final class MessagesPresenter extends _BasePresenter
{
  public function renderIndex(): void
  {
    $this->template->threads = $this->dbWrapper->getThreads($this->template->user['id']);
  }

  public function renderDetail($threadId)
  {
    $this->template->thread = $this->dbWrapper->getThread($threadId);
    $this->template->messages = $this->dbWrapper->getThreadMessages($threadId);

    $this->dbWrapper->saveAllRead($threadId);
  }

  public function createComponentContactForm(): UI\Form
  {
    $form = new UI\Form;

    $form->addHidden('threadId')
      ->setDefaultValue($this->template->thread->id ?? '');

    $form->addHidden('new')
      ->setDefaultValue(0);

    $form->addHidden('from')
      ->setDefaultValue('user');

    $form->addTextArea('message', 'Scrivi un messaggio')
      ->setRequired('Campo obbligatorio')
      ->setHtmlAttribute('placeholder', 'Scrivi un messaggio...')
      ->setHtmlAttribute('class', 'form-control');

    $form->addInvisibleReCaptcha('recaptcha')
      ->setMessage('Are you a bot?');

    $form->addSubmit('contact', 'Invia');

    $form->onSubmit[] = [$this, 'submitSendMessage'];

    return $form;
  }

  public function submitSendMessage(UI\Form $form): void
  {
    $values = $form->getValues();

    $result = $this->dbWrapper->addThreadMessage($values);

    if ($result === false) {
      $this->flashMessage("Messaggio non inviato, riprova.", "danger");
    }
    else {
      $this->flashMessage("Il tuo messaggio è stato inviato correttamente!", "success");
      $this->redirect('Messages:detail', $values->threadId);
    }
  }
}
