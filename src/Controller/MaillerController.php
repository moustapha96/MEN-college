<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\User;
use App\Service\MailerService;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\HttpFoundation\Request;
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





    public function sendMailCompteBloque(User $user, MailerService $mailer): Response
    {


        // $mail =  $mailer->sendMailCompteBloque(
        //     "Veuillez recevoir le rapport de " . $user->getFirstName() . " " . $user->getLastName(),
        //     $college->getNom(),
        //     $user->getEmail(),
        //     "khouma964@gmail.com",
        //     $rapport,
        //     $attachments,

        // );
        // dd($mail);

        $this->addFlash('success', "Mail test envoyer avec success");
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/mail-rapport', name: 'send_rapport', methods: ['POST'])]
    public function sendMailRapport(Rapport $rapport, Request $request, MailerService  $mailer): Response
    {
        $email = $request->request->get('email');

        $college =  $rapport->getCollege();
        $user = $rapport->getUser();
        $fichiers = $rapport->getFichier();


        $attachments = [];
        if (count($fichiers) > 0) {
            foreach ($fichiers as $key => $fiche) {
                $attachments[] = ["file" => $this->getParameter('pdf_directory') . '/' . $fiche, "name" => "fichier " . $key + 1];
            }
        }

        $mail =  $mailer->sendMail(
            "Veuillez recevoir le rapport de " . $user->getFirstName() . " " . $user->getLastName(),
            $college->getNom(),
            $email,
            "khouma964@gmail.com",
            $rapport,
            $attachments,
            $user
        );

        $this->addFlash('success', "Mail envoyer avec succès");
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
