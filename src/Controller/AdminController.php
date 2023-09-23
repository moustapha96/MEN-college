<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\College;
use App\Entity\Rapport;
use App\Form\CollegeType;
use App\Form\RapportType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CollegeRepository;
use App\Repository\RapportRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin',  name: "admin_")]
// #[AttributeIsGranted("ROLE_ADMIN", statusCode: 404, message: "Page non accéssible")]
class AdminController extends AbstractController
{

    public function __construct()
    {
    }


    #[Route('/',  name: "home")]
    public function index(
        CollegeRepository $collegeRepository,
        UserRepository $userRepository,
        RapportRepository $rapportRepository,

    ): Response {



        return $this->render("admin/dashboard/index.html.twig", [
            'titre' => 'Accueil Admin ',
            'rapports' => $rapportRepository->findAll(),
            'colleges' => $collegeRepository->findAll(),
            'users' =>  $userRepository->findAll()
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
            'rapports' => $rapportRepository->findAll()
        ]);
    }


    #[Route('/rapports/nouveau',  name: "rapport_nouveau", methods: ['GET', 'POST'])]
    public function NouveauRpport(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rapport = new Rapport();
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();

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
            return $this->redirectToRoute('admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/college/rapport_new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
            'college' => $college,
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
    public function edit(RapportRepository $rapportRepository, Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
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
}
