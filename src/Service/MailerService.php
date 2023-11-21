<?php

namespace App\Service;

use App\Entity\Rapport;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;


class MailerService
{

    private $config;

    private $mail;

    public function __construct(
        MailerInterface  $mailer,
        ConfigurationService $config
    ) {
        $this->mail = $mailer;
        $this->config = $config;
    }

    public function sendMailCompteBloque(User $user)
    {

        $emailIGEF = $this->config->get('email');
        $email = (new TemplatedEmail())
            ->from(new Address($emailIGEF, "IGEF"))
            ->to(new Address($user->getEmail()))
            ->cc(new Address("khouma964@gmail.com"))
            ->subject("Compte bolqué")
            ->htmlTemplate('emails/template_base_compte.html.twig')
            ->context([
                "message" => "Votre compte à été bloqué",
                "user" => $user,
                "autre" => "Merci de contactez l'administrateur pour plus d'informations",
                "type" => "compte_bloque"
            ]);

        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function sendMailCompteActive(User $user)
    {

        $emailIGEF = $this->config->get('email');
        $email = (new TemplatedEmail())
            ->from(new Address($emailIGEF, "IGEF"))
            ->to(new Address($user->getEmail()))
            ->cc(new Address("khouma964@gmail.com"))
            ->subject("Compte Activé")
            ->htmlTemplate('emails/template_base_compte.html.twig')
            ->context([
                "message" => "Votre compte à été activé",
                "user" => $user,
                "autre" => "Vous pouvez envoyer vos rapports tranquillement depuis la plateforme",
                "type" => "compte_active"
            ]);

        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function sendMailCompteCreer(User $user, string $password)
    {

        $emailIGEF = $this->config->get('email');
        $email = (new TemplatedEmail())
            ->from(new Address($emailIGEF, "IGEF"))
            ->to(new Address($user->getEmail()))
            ->cc(new Address("khouma964@gmail.com"))
            ->subject("Compte créé Avec succé")
            ->htmlTemplate('emails/template_base_compte.html.twig')
            ->context([
                "message" => "Votre compte à été créé avec succès",
                "user" => $user,
                "autre" => "Vous pouvez maintenant vous connecter sur la plateforme",
                "type" => "compte_creer",
                "password" => $password
            ]);

        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }


    public function sendMail(
        string $message,
        string $college,
        string $destinataire,
        string $destinatairecc,
        Rapport $rapport,
        array $attachments = [],
        User $user
    ) {

        $emailIGEF = $this->config->get('email');


        $email = (new TemplatedEmail())
            ->from(new Address($emailIGEF, "IGEF"))
            ->to(new Address($destinataire))
            ->cc(new Address($destinatairecc))
            ->subject("Rapport collège " . $college)
            ->htmlTemplate('emails/template_base.html.twig')
            ->context([
                'message' => $message,
                'prenom' => $user->getFirstName(),
                'nom' => $user->getLastName(),
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

        $emailIGEF = $this->config->get('email');
        $email = (new TemplatedEmail())
            ->from(new Address($emailIGEF, "IGEF"))
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
    }
}
