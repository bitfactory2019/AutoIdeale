<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use \Nette\Application\UI;
use \Nette\Application\Responses\JsonResponse;
use \Nette\Utils\DateTime;
use \App\Utils;

final class RequestsPresenter extends _BasePresenter
{
    public function createComponentRequestForm($requestId): UI\Form
    {
        $form = new UI\Form();

        $form->addSubmit('approved', 'Approva')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-fw fa-check-circle-o"></i> Approva');

        $form->addSubmit('refused', 'Rifiuta')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-fw fa-times-circle-o"></i> Rifiuta');

        $form->onSuccess[] = [$this, 'submitRequest'];

        return $form;
    }

    public function submitRequest(UI\Form $form, \stdClass $values): void
    {
        $requestId = $this->getHttpRequest()->getPost('requestId');

        $dbo = $this->db->table("posts_requests")
            ->where('id', $requestId);

        if ($form['approved']->isSubmittedBy()) {
            $status = 'approved';
            $message = "L'appuntamento del %s alle %s con %s è stato confermato correttamente!";
            $message_type = 'success';
        }
        elseif ($form['refused']->isSubmittedBy()) {
            $status = 'refused';
            $message = "L'appuntamento del %s alle %s con %s è stato rifiutato.";
            $message_type = 'info';
        }

        $dbo->update(['status' => $status]);

        $request = $this->dbWrapper->getRequest($requestId);

        $this->flashMessage(
            \sprintf(
                $message,
                DateTime::from($request['data']->date_time)->format("d/m/Y"),
                DateTime::from($request['data']->date_time)->format("H:i"),
                $request['data']->name
            ),
            $message_type
        );
        $this->redirect('Requests:index');
        return;
    }
}
