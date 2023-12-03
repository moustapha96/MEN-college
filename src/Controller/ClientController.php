<?php

namespace App\Controller;

use App\Entity\College;
use App\Entity\Publication;
use App\Entity\Rapport;
use App\Entity\User;
use App\Form\RapportType;
use App\Repository\CollegeRepository;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RapportRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/client',  name: "client_")]
#[IsGranted("ROLE_USER", statusCode: 404, message: "Page non accéssible")]
class ClientController extends AbstractController
{

    public function __construct()
    {
    }

    #[Route('/',  name: "home")]
    public function index(
        CollegeRepository $collegeRepository,
        RapportRepository $rapportRepository,
        UserRepository $userRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render("client/dashboard/index.html.twig", [
            'titre' => 'Inspecteur / Inspectrice',

            'rapports' => $rapportRepository->findBy(['user' => $user]),
            'colleges' => $collegeRepository->findAll(),
            'inspecteurs' => $userRepository->findBy(['college' => $user->getCollege()]),
            'rapports_college' =>  $rapportRepository->findBy(['user' => $user, 'college' => $user->getCollege()]),
            'rapports_colleges' =>  $rapportRepository->findBy(['college' => $user->getCollege()]),

            "rapports_en_attente" => $rapportRepository->findBy(['statut' => "EN ATTENTE", 'user' => $user]),
            "rapports_en_attentes" => $rapportRepository->findBy(['statut' => "EN ATTENTE", 'user' => $user, 'college' => $user->getCollege()]),

            "rapports_valider" => $rapportRepository->findBy(['statut' => "VALIDER", 'user' => $user]),
            "rapports_validers" => $rapportRepository->findBy(['statut' => "VALIDER", 'user' => $user, 'college' => $user->getCollege()]),

            "rapports_non_valider" => $rapportRepository->findBy(['statut' => "NON VALIDER", 'user' => $user]),
            "rapports_non_validers" => $rapportRepository->findBy(['statut' => "NON VALIDER", 'college' => $user->getCollege()]),
        ]);
    }


    #[Route('/colleges',  name: "college_liste")]
    public function ListeCollege(CollegeRepository $collegeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render("client/college/index.html.twig", [
            'titre' => 'Gestion des Collèges',
            'colleges' => $user->getCollege() ?  $collegeRepository->findBy(['id' =>  $user->getCollege()->getId()]) : []
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

        /** @var User $user */
        $user = $this->getUser();
        $rapport = new Rapport();
        $college = $collegeRepository->find($id);
        $rapport->setCollege($college);
        $rapport->setStatut("EN ATTENTE");
        $form = $this->createForm(RapportType::class, $rapport);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $rapport->setUser($user);

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . '_' . $rapport->getCollege()->getNom() . '_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }

            $rapport->setFichier($fichiers);


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
        /** @var User $user */
        $user = $this->getUser();
        $rapports = $rapportRepository->findBy(['college' => $user->getCollege()]);
        return $this->render("client/rapport/index.html.twig", [
            'titre' => 'Liste des rapports d\'activités',
            "rapports" => $rapports,
            "college" => $user->getCollege()
        ]);
    }


    #[Route('/rapports/nouveau',  name: "rapport_nouveau", methods: ['GET', 'POST'])]
    public function NouveauRpport(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $rapport = new Rapport();
        $rapport->setCollege($user->getCollege());
        $rapport->setStatut("EN ATTENTE");
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted()) {
            /** @var User $user */

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . '_' . $rapport->getCollege()->getNom() . '_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }

            $rapport->setFichier($fichiers);
            $rapport->setCollege($user->getCollege());
            $rapport->setUser($user);
            $entityManager->persist($rapport);
            $entityManager->flush();
            $this->addFlash('success', "Rapport d'activité enregistrer avec succès");
            return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }
        /** @var User $user */
        return $this->render('client/rapport/new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
            'titre' => "Nouveau Rapport d'activité ",
            "college" =>  $user->getCollege()
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

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted()) {

            $activiteFile = $form->get('activiteFichier')->getData();

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . '_' . $rapport->getCollege()->getNom() . '_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }

            $rapport->setFichier($fichiers);
            $entityManager->flush();
            $this->addFlash('success', "Rapport d'activité mise à jour avec succès");
            return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/rapport/edit.html.twig', [
            'titre' => "Mise à jour Rapport d'activité",
            'rapport' => $rapport,
            'form' => $form,
            "college" =>  $user->getCollege()
        ]);
    }


    #[Route('rapports/{id}/suppression', name: 'rapport_delete', methods: ['POST'])]
    public function delete(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rapport->getId(), $request->request->get('_token'))) {
            $rapport->setIsDeleted(true);
            $entityManager->flush();
            $this->addFlash('warning', "Suppression Rapport d'activité éffectif");
        }
        return $this->redirectToRoute('client_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }

    //fichier d'un rapport
    #[Route("/rapport/{id}/fichiers", name: "rapport_fichier", methods: ["GET"])]
    public function showFichierRapport(Request $request, Rapport $rapport): Response
    {
        return $this->render('client/rapport/fichier.html.twig', [
            'titre' => 'Liste Fichier',
            'rapport' => $rapport,
        ]);
    }

    // publication
    #[Route('/publication', name: 'publication_index')]
    public function indexPublication(PublicationRepository $publication): Response
    {

        $pubs = $publication->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('client/publication/index.html.twig', [
            'titre' => 'Publications',
            "publications" => $pubs
        ]);
    }

    // publication
    #[Route('/new-publication', name: 'publication_new')]
    public function indexNew(): Response
    {
        return $this->render('client/publication/new.html.twig', [
            'titre' => 'Nouvelle Publication',
        ]);
    }

    // publication
    #[Route('/save-publication', name: 'publication_save', methods: ['POST'])]
    public function indexSave(Request $request, EntityManagerInterface $em): Response
    {

        $titre = $request->request->get('titre');
        $contenu = $request->request->get('contenu');
        /** @var User user  */
        $user = $this->getUser();
        $publication = new Publication();

        $publication->setDestinataire($request->request->get('destinataire'));
        $publication->setTitre($titre);
        $publication->setContenu($contenu);
        $publication->setUser($user);

        $em->persist($publication);
        $em->flush();

        $this->addFlash('success', "Publication ajoutée avec succès");
        return $this->redirectToRoute('client_publication_index', [], Response::HTTP_SEE_OTHER);
    }

    //suppression
    #[Route('/suppression/{id}', name: 'publication_delete', methods: ['GET'])]
    public function deletePub(Publication $pub, EntityManagerInterface $em): Response
    {

        $em->remove($pub);
        $em->flush();
        $this->addFlash('success', "Suppréssion publication réussie");
        return $this->redirectToRoute('client_publication_index', [], Response::HTTP_SEE_OTHER);
    }
}
