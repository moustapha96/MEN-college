<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\College;
use App\Entity\Rapport;
use App\Entity\User;
use App\Form\CollegeType;
use App\Form\RapportType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CollegeRepository;
use App\Repository\RapportRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\ChatGPTService;


#[Route('/admin',  name: "admin_")]
// #[AttributeIsGranted("ROLE_ADMIN", statusCode: 404, message: "Page non accéssible")]
class AdminController extends AbstractController
{

    private $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }


    #[Route('/',  name: "home")]
    public function index(
        CollegeRepository $collegeRepository,
        UserRepository $userRepository,
        RapportRepository $rapportRepository,

    ): Response {



        return $this->render("admin/dashboard/index.html.twig", [
            'titre' => 'Dashboard Admin ',
            'rapports' => $rapportRepository->findAll(),
            'rapports_valide' => $rapportRepository->findBy(['isDeleted' => 0]),
            'rapports_deleted' => $rapportRepository->findBy(['isDeleted' => 1]),
            'colleges' => $collegeRepository->findAll(),
            'users' =>  $userRepository->findAll(),
            "rapports_en_attente" => $rapportRepository->findBy(['statut' => "EN ATTENTE"]),
            "rapports_valider" => $rapportRepository->findBy(['statut' => "VALIDER"]),
            "rapports_non_valider" => $rapportRepository->findBy(['statut' => "NON VALIDER"]),
        ]);
    }


    #[Route('/colleges',  name: "college_liste")]
    public function ListeCollege(CollegeRepository $collegeRepository): Response
    {
        return $this->render("admin/college/index.html.twig", [
            'titre' => 'Gestion des Collèges',
            'colleges' => $collegeRepository->findAll()
        ]);
    }


    #[Route('/colleges/nouveau', name: 'college_nouveau', methods: ['GET', 'POST'])]
    public function NouveauCollege(Request $request, EntityManagerInterface $entityManager, CollegeRepository $collegeRepository): Response
    {

        $college = new College();
        $form = $this->createForm(CollegeType::class, $college);

        $data = $request->request->all();
        if ($data) {

            $college = new College();
            $college->setNom($data['nom']);
            $college->setDescription($data['description']);
            $collecteNom = $collegeRepository->findBy(['nom' => $data['nom']]);
            if ($collecteNom) {
                $this->addFlash('warning', "Collège avec ce Nom existe  !! ");
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            $entityManager->persist($college);
            $entityManager->flush();
            $this->addFlash('success', "Collège créé avec succés");
            return $this->redirectToRoute('admin_college_liste');
        }

        return $this->render('admin/college/new.html.twig', [
            'titre' => 'Nouveau Collège',
            'college' => $college,

        ]);
    }

    #[Route('/colleges/{id}/show', name: 'college_show', methods: ['GET'])]
    public function showCollege(College $college): Response
    {
        return $this->render('admin/college/show.html.twig', [
            'titre' => 'Detail Collège',
            'college' => $college,
        ]);
    }

    #[Route('/rapports/{id}/inspecteur', name: 'college_rapport_client_show', methods: ['GET'])]
    public function showRapportClient(
        User $user,
        RapportRepository $rapportRepository
    ): Response {
        return $this->render('admin/college/liste_rapport_client.html.twig', [
            'titre' => 'Rapports de',
            'rapports' => $rapportRepository->findBy(['user' => $user]),
            "user" =>  $user
        ]);
    }

    //fichier d'un rapport
    #[Route("rapport/{id}/fichiers", name: "rapport_fichier", methods: ["GET"])]
    public function showFichierRapport(Request $request, Rapport $rapport): Response
    {
        return $this->render('admin/rapport/fichier.html.twig', [
            'titre' => 'Liste Fichier',
            'rapport' => $rapport,
        ]);
    }

    #[Route('/colleges/{id}/rapport', name: 'college_rapport', methods: ['GET'])]
    public function showCollegeRapport(College $college, RapportRepository $rapportRepository): Response
    {
        $rapports = $rapportRepository->findBy(['college' => $college]);

        return $this->render('admin/college/rapport.html.twig', [
            'titre' => 'Liste Rapports Collège => ' . $college->getNom(),
            'college' => $college,
            'rapports' => $rapports
        ]);
    }


    #[Route('/college/rapports/{id}/show',  name: "college_rapport_show", methods: ['GET'])]
    public function showRapport(Rapport $rapport): Response
    {
        return $this->render('admin/college/rapport_show.html.twig', [
            'rapport' => $rapport,
            'college' => $rapport->getCollege(),
            'titre' => "Détail Rapport d'activité",
        ]);
    }


    #[Route('/colleges/{id}/edit', name: 'college_edit', methods: ['GET', 'POST'])]
    public function editCollege(Request $request, int $id, CollegeRepository $collegeRepository, EntityManagerInterface $entityManager): Response
    {

        $college = $collegeRepository->find($id);
        $data = $request->request->all();
        if ($data) {


            $college->setNom($data['nom']);
            $college->setDescription($data['description']);

            $entityManager->persist($college);
            $entityManager->flush();
            $this->addFlash('success', "Mise à jour collège effectif");
            return $this->redirectToRoute('admin_college_liste');
        }

        return $this->render('admin/college/edit.html.twig', [
            'titre' => 'Mise a jour Collège',
            'college' => $college,
        ]);
    }

    #[Route('/colleges/{id}/delete', name: 'college_delete', methods: ['POST'])]
    public function deleteCollege(Request $request, College $college, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $college->getId(), $request->request->get('_token'))) {
            $entityManager->remove($college);
            $entityManager->flush();
            $this->addFlash('success', "Suppréssion effectif");
        }
        return $this->redirectToRoute('admin_college_liste', [], Response::HTTP_SEE_OTHER);
    }


    #---------------------------------------------
    #[Route('/rapports',  name: "rapport_liste")]
    public function ListeRpport(RapportRepository $rapportRepository): Response
    {
        return $this->render("admin/rapport/index.html.twig", [
            'titre' => 'Gestion des Rapports d\'Activités',
            'rapports' => $rapportRepository->findAll(['orderBy' => ['createdAt' => 'DESC']])
        ]);
    }


    #[Route('/rapports/nouveau',  name: "rapport_nouveau", methods: ['GET', 'POST'])]
    public function NouveauRpport(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $rapport = new Rapport();
        $rapport->setStatut("EN ATTENTE");
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $user = $this->getUser();
            $rapport->setUser($user);

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }

            $rapport->setFichier($fichiers);
            $entityManager->persist($rapport);
            $entityManager->flush();
            $this->addFlash('success', "Rapport d'activité créé avec succés ");
            return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rapport/new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
            'titre' => "Nouveau Rapport d'Activité"
        ]);
    }



    #[Route('/rapports/{id}/commenter',  name: "rapport_comment", methods: ['GET', 'POST'])]

    public function commneterRapport(
        Request $request,
        EntityManagerInterface $entityManager,
        Rapport $rapport
    ): Response {
        $commentaire = $request->request->get('commentaire');

        $referer = $request->headers->get('referer');

        $rapport->setComment($commentaire);
        $entityManager->persist($rapport);
        $entityManager->flush();
        $this->addFlash('success', "Rapport d'activité créé avec succés ");

        return $this->redirect($referer);
    }

    #[Route('/rapports/{id}/nouveau',  name: "college_rapport_nouveau", methods: ['GET', 'POST'])]
    public function NouveauRpportCollege(
        $id,
        Request $request,
        CollegeRepository $collegeRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $this->getUser();
        $rapport = new Rapport();
        $college = $collegeRepository->find($id);
        $rapport->setCollege($college);
        $form = $this->createForm(RapportType::class, ['statut' => "EN ATTENTE"]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $rapport->setUser($user);

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }
            $rapport->setFichier($fichiers);
            $entityManager->persist($rapport);
            $this->addFlash('success', "Rapport enregistrer avec succés");
            $entityManager->flush();
            return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/college/rapport_new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
            'college' => $college,
            'titre' => "Nouveau Rapport d'Activité"
        ]);
    }

    #[Route('/rapports/{id}/synthese',  name: "college_synthese", methods: ['GET'])]
    public function syntheseRapportCollege(
        College $college,

    ): Response {



        $allDataText = "je veux un synthese de ces activités \n ";
        $rapports = $college->getRapports();

        foreach ($rapports as $r) {
            $allDataText .= $r->getActivite() . "\n";
            $allDataText .= $r->getResultat() . "\n";
            $allDataText .= $r->getDescription() . "\n";
        }

        $responseText = $this->chatGPTService->generateResponse($allDataText);
        dd($allDataText);

        return $this->render('admin/college/rapport_new.html.twig', [

            'titre' => "Nouveau Rapport d'Activité"
        ]);
    }


    #[Route('/rapports/{id}/show',  name: "rapport_show", methods: ['GET'])]
    public function show(Rapport $rapport): Response
    {
        return $this->render('admin/rapport/show.html.twig', [
            'rapport' => $rapport,
            'titre' => "Détail Rapport d'activité",
        ]);
    }


    #[Route('/rapports/{id}/edit', name: 'rapport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {

        $rapportAvant = clone $rapport;

        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $fichiers = [];
            $files = $form->get('fichier')->getData();
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = count($fichiers) . $file->getFilename() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('pdf_directory'), $fileName);
                    $fichiers[] = $fileName;
                }
            }

            $rapport->setFichier($fichiers);
            $entityManager->flush();
            $this->addFlash('success', "Mise à jour rapport d'activité effectif");
            return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rapport/edit.html.twig', [
            'titre' => "Mise à jour Rapport d'activité",
            'rapport' => $rapport,
            'form' => $form,
        ]);
    }


    #[Route('/rapports/{id}/suppression', name: 'rapport_delete', methods: ['POST'])]
    public function delete(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rapport->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rapport);
            $entityManager->flush();
            $this->addFlash('warning', "Suppréssion éffectif");
        }
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/rapports/{id}/active', name: 'rapport_activer', methods: ['GET'])]
    public function SetEnableDelete(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        if ($rapport) {
            $rapport->setIsDeleted(!$rapport->isIsDeleted());
            $entityManager->persist($rapport);
            $entityManager->flush();
            $rapport->isIsDeleted() ?   $this->addFlash('success', "Rapport activé") :
                $this->addFlash('danger', "Rapport archivé");
        }
        return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/rapports/{id}/state', name: 'rapport_update_state', methods: ['POST'])]
    public function changeState(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        $state = $request->request->all()['state'];

        if ($rapport) {
            $rapport->setStatut($state);
            if ($state == "NON VALIDER") {
                $rapport->setIsDeleted(true);
            } else {
                $rapport->setIsDeleted(false);
            }

            $entityManager->persist($rapport);
            $entityManager->flush();
            if ($state == "EN ATTENTE") {
                $this->addFlash('warning', "Rapport " . $state);
            } else if ($state == "VALIDER") {
                $this->addFlash('success', "Rapport " . $state);
            } else {
                $this->addFlash('danger', "Rapport " . $state);
            }
        }
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }
}
