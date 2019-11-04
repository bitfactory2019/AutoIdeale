<?php

namespace App\Utils;

use Nette\Mail;

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
    
    public function sendNewUser($values)
    {
        $template = new \Latte\Engine;
        $mail = new Mail\Message;
        $config = $this->presenter->context->getParameters();

        $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
            ->addTo($values->email)
            ->setSubject('Nuova registrazione')
            ->setHtmlBody($template->renderToString($config['templateEmailsDir'].'newUser.latte', ["args" => $values]));

        $this->mailer->send($mail);
    }
}
