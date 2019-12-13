<?php

namespace App\Utils;

use Nette\Mail;
use Nette\Http\Url;

class EmailWrapper
{
    private $presenter;
    private $db;
    private $mailer;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
        $this->db = $this->presenter->getDbService();
        $this->mailer = new Mail\SendmailMailer;
    }
    
    public function sendNewUserConfirmation($values)
    {
        $template = new \Latte\Engine;
        $mail = new Mail\Message;
        $config = $this->presenter->context->getParameters();

        $url = $this->_getBaseUrl();
        $url->path .= '/registration/confirmation';
        $url->query = 'account='.base64_encode($values->email);

        $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
            ->addTo($values->email)
            ->setHtmlBody(
                $template->renderToString(
                    $config['templateEmailsDir'].'newUserConfirmation.latte', [
                    "args" => $values,
                    "confirmationUrl" => $url
                ])
            );

        $this->mailer->send($mail);
    }

    private function _getBaseUrl()
    {
        $url = new Url($this->presenter->getHttpRequest()->getUrl());
        $url->path = '/AutoIdeale/www';

        return $url;
    }
}
