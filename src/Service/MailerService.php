<?php

namespace App\Service;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{

    private $mail;

    public function __construct(
        MailerInterface  $mailer
    ) {
        $this->mail = $mailer;
    }

    public function sendMail(
        string $message,
        $detail,
        string $destinataire,
        string $destinatairecc,
        $objet
    ) {


        $email = (new Email())
            ->from('moustaphakhouma964@gmail.com')
            ->to('khouma964@gmail.com')
            ->subject('Sujet de l\'e-mail')
            ->text('Contenu du message');

        // Envoyez l'e-mail
        $this->mail->send($email);


        $email = (new TemplatedEmail())
            ->from(new Address("moustaphakhouma964@gmail.com", 'MEN'))
            ->to(new Address($destinataire))
            ->cc(new Address($destinatairecc))
            ->subject($objet)
            ->htmlTemplate('emails/template_base.html.twig')
            ->context([
                'message' => $message,
                'prenom' => "Seydou",
                'nom' =>
                "Khouma",
                'objet' => "Seydou",
            ]);

        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }

        // return true;
    }

    public function sendVerifEmail(
        string $message,
        $detail,
        string $destinataire,
        string $destinatairecc,
        $objet
    ) {
        $email = (new TemplatedEmail())
            ->from(new Address("men-rapport@gmail.com", 'MEN'))
            ->to(new Address($destinataire))
            ->cc(new Address($destinatairecc))
            ->subject($objet)
            ->htmlTemplate('registration/confirmation_email.html.twig')
            ->context([
                'message' => $message,
                'detail' => $detail
            ]);
        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }

        return true;
    }
}
