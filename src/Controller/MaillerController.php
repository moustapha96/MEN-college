<?php

namespace App\Controller;

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
        $mailer->sendMail("Ce mail est un test", "", "khouma964@gmail.com", "alhusseinkhouma0@gmail.com", "");

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
