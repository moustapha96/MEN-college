<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\User;
use App\Service\MailerService;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use DoctrineExtensions\Query\Mysql\Date;


use Symfony\Component\Mime\Part\DataPart;


#[Route('/mailler',  name: "app_mailler_")]
class MaillerController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('mailler/index.html.twig', [
            'controller_name' => 'MaillerController',
        ]);
    }


    #[Route('/test', name: 'test')]
    public function testSendMail(MailerService  $mailer): Response
    {
        $mailer->sendMail(
            "Ce mail est un test",
            "",
            "khouma964@gmail.com",
            "alhusseinkhouma0@gmail.com",
            ""
        );

        $this->addFlash('success', "Mail test envoyer avec success");
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }


    public function sendMailCompteBloque(User $user, MailerService $mailer): Response
    {
       
       
        $mail =  $mailer->sendMailCompteBloque(
            "Veuillez recevoir le rapport de " . $user->getFirstName() . " " . $user->getLastName(),
            $college->getNom(),
            $user->getEmail(),
            "khouma964@gmail.com",
            $rapport,
            $attachments
        );
        // dd($mail);

        $this->addFlash('success', "Mail test envoyer avec success");
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/mail-rapport', name: 'send_rapport')]
    public function sendMailRapport(Rapport $rapport, MailerService  $mailer): Response
    {
        $college =  $rapport->getCollege();
        $user = $rapport->getUser();
        $fichier_resultat = $rapport->getResultatFichier();
        $fichier_desciption = $rapport->getDescriptionFichier();
        $fichier_activite = $rapport->getActiviteFichier();

        $attachments = [];

        if ($fichier_resultat) {
            $attachments[] = ["file" => $this->getParameter('pdf_directory') . '/' . $fichier_resultat, "name" => "fichier resultat"];
        }
        if ($fichier_desciption) {
            $attachments[] =
                ["file" => $this->getParameter('pdf_directory') . '/' . $fichier_desciption, "name" => "fichier description"];
        }
        if ($fichier_activite) {
            $attachments[] =
                ["file" => $this->getParameter('pdf_directory') . '/' . $fichier_activite, "name" => "fichier activité"];
        }

        $mail =  $mailer->sendMail(
            "Veuillez recevoir le rapport de " . $user->getFirstName() . " " . $user->getLastName(),
            $college->getNom(),
            $user->getEmail(),
            "khouma964@gmail.com",
            $rapport,
            $attachments
        );
        // dd($mail);

        $this->addFlash('success', "Mail test envoyer avec success");
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/sendMail', name: 'send')]
    public function sendMail(MailerInterface  $mailer, $request): Response
    {

        $data = json_decode($request->getContent(), true);
        $sender = $data['sender'];
        $receiver = $data['receiver'];
        $objet = $data['objet'];
        $message = $data['message'];
        $prenom = $data['prenom'];
        $nom = $data['nom'];

        $email = (new TemplatedEmail())
            ->from(new Address($sender, $prenom . "  " . $nom))
            ->to(new Address($receiver))
            ->subject($objet)
            ->htmlTemplate('emails/mail_template.html.twig')
            ->context([
                'prenom' => $prenom,
                "nom" => $nom,
                'message' => $message,
                'date' =>  new DateTime('now')
            ]);
        $mailer->send($email);
        $this->addFlash('success', "Mail envoyer avec success");
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
