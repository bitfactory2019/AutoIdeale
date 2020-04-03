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
        $url->path = '/registration/confirmation';
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
        return new Url($this->presenter->getHttpRequest()->getUrl());
    }

    public function sendAddWishlist($post)
    {
        $template = new \Latte\Engine;
        $mail = new Mail\Message;
        $config = $this->presenter->context->getParameters();

        $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
            ->addTo($post['data']->users->email)
            ->setHtmlBody(
                $template->renderToString(
                    $config['templateEmailsDir'].'addWishlist.latte', [
                    "post" => $post
                ])
            );

        try {
          $this->mailer->send($mail);
        }
        catch (\Exception $e) {
        }
    }

    public function sendUserNewPassword($user, $password)
    {
        $template = new \Latte\Engine;
        $mail = new Mail\Message;
        $config = $this->presenter->context->getParameters();

        $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
            ->addTo($user->email)
            ->setHtmlBody(
                $template->renderToString(
                    $config['templateEmailsDir'].'userNewPassword.latte', [
                    "user" => $user,
                    "password" => $password,
                    "accountLink" => $this->presenter->link("//Account:index")
                ])
            );

        try {
          $this->mailer->send($mail);
        }
        catch (\Exception $e) {
        }
    }

    public function sendNewMessageUser($threadId)
    {
      $thread = $this->db->table("car_posts_threads")->get($threadId);
      $lastMessage = $thread->related('car_posts_threads_messages')
        ->where('from', 'visitor')
        ->where('new', true)
        ->order('datetime DESC')
        ->limit(1)
        ->fetch();

      $template = new \Latte\Engine;
      $mail = new Mail\Message;
      $config = $this->presenter->context->getParameters();
      $user = $this->db->table('users')->get($thread->car_posts->users->id);

      $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
        ->addTo($user->email)
        ->setHtmlBody(
          $template->renderToString($config['templateEmailsDir'].'newMessageUser.latte', [
            "user" => $thread->car_posts->users,
            "thread" => $thread,
            "message" => $lastMessage,
            "postLink" => $this->presenter->link("//Listing:detail", ["postId" => $thread->car_posts_id]),
            "messagesLink" => $this->presenter->link("//Admin:Messages:detail", $threadId)
          ])
        );

      try {
        $this->mailer->send($mail);
      }
      catch (\Exception $e) {
        dump($e);
        exit;
      }
    }

    public function sendNewMessageVisitor($threadId)
    {
      $thread = $this->db->table("car_posts_threads")->get($threadId);
      $lastMessage = $thread->related('car_posts_threads_messages')
        ->where('from', 'visitor')
        ->where('new', true)
        ->order('datetime DESC')
        ->limit(1)
        ->fetch();

      $template = new \Latte\Engine;
      $mail = new Mail\Message;
      $config = $this->presenter->context->getParameters();
      $user = $this->db->table('users')->get($thread->car_posts->users->id);

      $mail->setFrom('AutoIdeale <noreply@autoideale.it>')
        ->addTo($user->email)
        ->setHtmlBody(
          $template->renderToString($config['templateEmailsDir'].'newMessageVisitor.latte', [
            "user" => $thread->car_posts->users,
            "thread" => $thread,
            "message" => $lastMessage,
            "postLink" => $this->presenter->link("//Listing:detail", ["postId" => $thread->car_posts_id]),
            "messagesLink" => $this->presenter->link("//Thread:detail", ["hash" => $hash])
          ])
        );

      try {
        $this->mailer->send($mail);
      }
      catch (\Exception $e) {
        dump($e);
        exit;
      }
    }
}
