<?php

namespace App\Service;

use App\Entity\Rapport;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;


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
        string $college,
        string $destinataire,
        string $destinatairecc,
        Rapport $rapport,
        array $attachments = []
    ) {

        $email = (new TemplatedEmail())
            ->from(new Address("moustaphakhouma964@gmail.com", 'MEN'))
            ->to(new Address($destinataire))
            ->cc(new Address($destinatairecc))
            ->subject("Rapport collège " . $college)
            ->htmlTemplate('emails/template_base.html.twig')
            ->context([
                'message' => $message,
                'prenom' => "Pape",
                'nom' => "Khouma",
                'objet' => "Rapport college " . $college,
                'rapport' => $rapport
            ]);


        foreach ($attachments as $attachment) {
            $email->addPart(new DataPart(new File($attachment['file']), $attachment['name']));
        }

        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
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
