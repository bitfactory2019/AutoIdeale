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
        $this->template->messages = $this->dbWrapper->getMessages($this->getAdminUser()['id']);
    }
}
