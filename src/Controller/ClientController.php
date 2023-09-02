<?php

namespace App\Controller;

use App\Entity\College;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Rapport;
use App\Entity\User;
use App\Form\RapportType;
use App\Form\RapportTypeEdit;
use App\Repository\CollegeRepository;
use App\Repository\EmpruntRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LivreRepository;
use App\Repository\RapportRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/client',  name: "client_")]
#[AttributeIsGranted("ROLE_USER", statusCode: 404, message: "Page non accéssible")]
class ClientController extends AbstractController
{

    public function __construct()
    {
    }

    #[Route('/',  name: "home")]
    public function index(
        CollegeRepository $collegeRepository,
        // UserRepository $userRepository,
        RapportRepository $rapportRepository
    ): Response {
        return $this->render("client/dashboard/index.html.twig", [
            'titre' => 'Inspecteur / Inspectrice',
            'rapports' => $rapportRepository->findAll(),
            'colleges' => $collegeRepository->findAll(),
            // 'users' =>  $userRepository->findAll()
        ]);
    }




    #[Route('/colleges',  name: "college_liste")]
    public function ListeCollege(CollegeRepository $collegeRepository): Response
    {
        return $this->render("client/college/index.html.twig", [
            'titre' => 'Gestion des Collèges',
            'colleges' => $collegeRepository->findAll()
        ]);
    }

    #[Route('/colleges/nouveau',  name: "college_nouveau")]
    public function NouveauCollege(CollegeRepository $collegeRepository): Response
    {
        return $this->render("client/college/index.html.twig", [
            'titre' => 'Gestion des Collèges',
            'colleges' => $collegeRepository->findAll()
        ]);
    }

    #[Route('/colleges/{id}/show', name: 'college_show', methods: ['GET'])]
    public function showCollege(College $college): Response
    {
        return $this->render('client/college/show.html.twig', [
            'titre' => 'Detail Collège',
            'college' => $college,
        ]);
    }

    #[Route('/colleges/rapport/{id}/nouveau', name: 'college_rapport_nouveau', methods: ['GET', 'POST'])]
    public function ajoutRapport(
        $id,
        Request $request,
        CollegeRepository $collegeRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $this->getUser();
        $rapport = new Rapport();
        $college = $collegeRepository->find($id);
        $rapport->setCollege($college);
        $form = $this->createForm(RapportType::class, $rapport);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $rapport->setUser($user);
            $activiteFile = $form->get('activiteFichier')->getData();
            if ($activiteFile instanceof UploadedFile) {
                $activiteFilename = uniqid() . '.' . $activiteFile->guessExtension();
                $activiteFile->move($this->getParameter('pdf_directory'), $activiteFilename);
                $rapport->setActiviteFichier($activiteFilename);
            }
            $descriptionFile = $form->get('descriptionFichier')->getData();
            if ($descriptionFile instanceof UploadedFile) {
                $descriptionFilename = uniqid() . '.' . $descriptionFile->guessExtension();
                $descriptionFile->move($this->getParameter('pdf_directory'), $descriptionFilename);
                $rapport->setDescriptionFichier($descriptionFilename);
            }

            $resultatFile = $form->get('resultatFichier')->getData();
            if ($resultatFile instanceof UploadedFile) {
                $resultatFilename = uniqid() . '.' . $resultatFile->guessExtension();
                $resultatFile->move($this->getParameter('pdf_directory'), $resultatFilename);
                $rapport->setResultatFichier($resultatFilename);
            }


            $entityManager->persist($rapport);
            $this->addFlash('success', "Rapport enregistrer avec succés");
            $entityManager->flush();
            return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/college/rapport.html.twig', [
            'titre' => "Ajout d'un Rapport d'activité",
            'college' => $college,
            'form' => $form
        ]);
    }

    #[Route('/rapports',  name: "rapport_liste")]
    public function ListeRpport(RapportRepository $rapportRepository): Response
    {
        $user = $this->getUser();
        $rapports = $rapportRepository->findBy(['user' => $user]);
        return $this->render("client/rapport/index.html.twig", [
            'titre' => 'Liste de vos rapports d\'activités',
            'rapports' => $rapports
        ]);
    }


    #[Route('/rapports/nouveau',  name: "rapport_nouveau", methods: ['GET', 'POST'])]
    public function NouveauRpport(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rapport = new Rapport();
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $rapport->setUser($user);
            $entityManager->persist($rapport);
            $entityManager->flush();
            $this->addFlash('success', "Rapport d'activité enregistrer avec succès");
            return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('client/rapport/new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
            'titre' => "Nouveau Rapport d'activité"
        ]);
    }

    #[Route('/rapports/{id}',  name: "rapport_show", methods: ['GET'])]
    public function show(Rapport $rapport): Response
    {
        return $this->render('client/rapport/show.html.twig', [
            'titre' => "Détail Rapport d'activité",
            'rapport' => $rapport,
        ]);
    }

    #[Route('/rapports/{id}/edit', name: 'rapport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {

        $rapportAvant = clone $rapport;
        $rapport->setActiviteFichier(null);
        $rapport->setResultatFichier(null);
        $rapport->setDescriptionFichier(null);

        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $activiteFile = $form->get('activiteFichier')->getData();

            if (!$rapport->getActiviteFichier()) {
                $rapport->setActiviteFichier($rapportAvant->getActiviteFichier());
            } else {
                if ($activiteFile instanceof UploadedFile || $activiteFile != null) {
                    $activiteFilename = uniqid() . '.' . $activiteFile->guessExtension();
                    $activiteFile->move($this->getParameter('pdf_directory'), $activiteFilename);
                    $rapport->setActiviteFichier($activiteFilename);
                }
            }


            $descriptionFile = $form->get('descriptionFichier')->getData();

            if (!$rapport->getDescriptionFichier()) {
                $rapport->setDescriptionFichier($rapportAvant->getDescriptionFichier());
            } else {
                if ($descriptionFile instanceof UploadedFile || $descriptionFile != null) {
                    $descriptionFilename = uniqid() . '.' . $descriptionFile->guessExtension();
                    $descriptionFile->move($this->getParameter('pdf_directory'), $descriptionFilename);
                    $rapport->setDescriptionFichier($descriptionFilename);
                }
            }

            $resultatFile = $form->get('resultatFichier')->getData();

            if (!$rapport->getResultatFichier()) {
                $rapport->setResultatFichier($rapportAvant->getResultatFichier());
            } else {
                if ($resultatFile instanceof UploadedFile || $resultatFile != null) {
                    $resultatFilename = uniqid() . '.' . $resultatFile->guessExtension();
                    $resultatFile->move($this->getParameter('pdf_directory'), $resultatFilename);
                    $rapport->setResultatFichier($resultatFilename);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', "Rapport d'activité mise à jour avec succès");
            return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/rapport/edit.html.twig', [
            'titre' => "Mise à jour Rapport d'activité",
            'rapport' => $rapport,
            'form' => $form,
        ]);
    }


    #[Route('rapports/{id}/suppression', name: 'rapport_delete', methods: ['POST'])]
    public function delete(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rapport->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rapport);
            $entityManager->flush();
            $this->addFlash('warning', "Suppression Rapport d'activité éffectif");
        }
        return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }
}
