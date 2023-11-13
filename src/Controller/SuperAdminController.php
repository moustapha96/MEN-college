<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\College;
use App\Entity\Configuration;
use App\Entity\Rapport;
use App\Entity\User;
use App\Form\CollegeType;
use App\Form\RapportType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CollegeRepository;
use App\Repository\ConfigurationRepository;
use App\Repository\RapportRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\ChatGPTService;
use App\Service\MailerService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/super_admin',  name: "super_admin_")]
// #[AttributeIsGranted("ROLE_ADMIN", statusCode: 404, message: "Page non accéssible")]
class SuperAdminController extends AbstractController
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
        return $this->render("super_admin/dashboard/index.html.twig", [
            'titre' => 'Accueil Admin ',
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
        return $this->render("super_admin/college/index.html.twig", [
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
            return $this->redirectToRoute('super_admin_college_liste');
        }

        return $this->render('super_admin/college/new.html.twig', [
            'titre' => 'Nouveau Collège',
            'college' => $college,

        ]);
    }

    #[Route('/colleges/{id}/show', name: 'college_show', methods: ['GET'])]
    public function showCollege(College $college): Response
    {
        return $this->render('super_admin/college/show.html.twig', [
            'titre' => 'Detail Collège',
            'college' => $college,
        ]);
    }

    #[Route('/rapports/{id}/inspecteur', name: 'college_rapport_client_show', methods: ['GET'])]
    public function showRapportClient(
        User $user,
        RapportRepository $rapportRepository
    ): Response {
        return $this->render('super_admin/college/liste_rapport_client.html.twig', [
            'titre' => 'Rapports de',
            'rapports' => $rapportRepository->findBy(['user' => $user], ['orderBy' => ['createdAt' => 'DESC']]),
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

        return $this->render('super_admin/college/rapport.html.twig', [
            'titre' => 'Liste Rapports Collège => ' . $college->getNom(),
            'college' => $college,
            'rapports' => $rapports
        ]);
    }


    #[Route('/college/rapports/{id}/show',  name: "college_rapport_show", methods: ['GET'])]
    public function showRapport(Rapport $rapport): Response
    {
        return $this->render('super_admin/college/rapport_show.html.twig', [
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

        return $this->render('super_admin/college/edit.html.twig', [
            'titre' => 'Mise a jour Collège',
            'college' => $college,
        ]);
    }

    #[Route('/colleges/{id}/delete', name: 'college_delete', methods: ['GET'])]
    public function deleteCollege(Request $request, College $college, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($college);
        $entityManager->flush();
        $this->addFlash('success', "Suppréssion effectif");
        return $this->redirectToRoute('super_admin_college_liste', [], Response::HTTP_SEE_OTHER);
    }


    #---------------------------------------------
    #[Route('/rapports',  name: "rapport_liste")]
    public function ListeRpport(RapportRepository $rapportRepository): Response
    {
        return $this->render("super_admin/rapport/index.html.twig", [
            'titre' => 'Gestion des Rapports d\'Activités',
            'rapports' => $rapportRepository->findAll()
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
            return $this->redirectToRoute('super_admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/rapport/new.html.twig', [
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
            return $this->redirectToRoute('super_admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/college/rapport_new.html.twig', [
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

        return $this->render('super_admin/college/rapport_new.html.twig', [

            'titre' => "Nouveau Rapport d'Activité"
        ]);
    }


    #[Route('/rapports/{id}/show',  name: "rapport_show", methods: ['GET'])]
    public function show(Rapport $rapport): Response
    {
        return $this->render('super_admin/rapport/show.html.twig', [
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
            return $this->redirectToRoute('super_admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/rapport/edit.html.twig', [
            'titre' => "Mise à jour Rapport d'activité",
            'rapport' => $rapport,
            'form' => $form,
        ]);
    }


    #[Route('/rapports/{id}/delete', name: 'rapport_delete', methods: ['GET'])]
    public function delete(Request $request, Rapport $rapport, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($rapport);
        $entityManager->flush();
        $this->addFlash('warning', "Suppréssion éffectif");
        return $this->redirectToRoute('super_admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
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
        return $this->redirectToRoute('super_admin_rapport_liste', [], Response::HTTP_SEE_OTHER);
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


    #[Route('/inspecteurs', name: 'user_index', methods: ['GET'])]
    public function indexUser(
        UserRepository $userRepository,
        CollegeRepository $collegeRepository
    ): Response {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'titre' => "Liste des Inspecteurs",
            "colleges" => $collegeRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function newUser(
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setAvatar('avatar.jpeg');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    'password'
                )
            );

            $user->setStatus("ACTIVE");

            $userByEmail = $userRepository->findOneBy(['email' => $form->getData('email')]);
            if ($userByEmail) {
                $this->addFlash('danger', 'Cette adresse email est déjà utilisé.');
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }

            $userByMatricule = $userRepository->findOneBy(['matricule' => $form->getData('matricule')]);
            if ($userByMatricule) {
                $this->addFlash('danger', 'Ce matricule est déjà utilisé.');
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }

            $userByPhone = $userRepository->findOneBy(['phone' => $form->getData('phone')]);
            if ($userByPhone) {
                $this->addFlash('danger', 'Ce numéro de téléphone est déjà utilisé.');
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Inspecteur ajouté avec succés");
            return $this->redirectToRoute('super_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'titre' => "Nouveau Inspecteur"
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function showUser(User $user): Response
    {
        return $this->render('super_admin/user/show.html.twig', [
            'user' => $user,
            'titre' => "Détail de l'inspecteur"
        ]);
    }



    #[Route('/{id}/acount', name: 'user_acount', methods: ['GET'])]
    public function editAccount(
        User $user,
        EntityManagerInterface $entityManager,
        MailerService $mailerService
    ): Response {
        $user->setEnabled(!$user->getEnabled());

        $entityManager->persist($user);
        $entityManager->flush();
        $user->getEnabled() == true ?
            $this->addFlash('success', "Compte activé avec succés ") :
            $this->addFlash('warning', "Compte desactivé avec succés ");

        $user->getEnabled() ? $mailerService->sendMailCompteActive($user) : $mailerService->sendMailCompteBloque($user);

        return $this->redirectToRoute('super_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        Request $request,
        UserRepository $userRepository,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $roles = $form->get('roles')->getData();
            $user->setRoles(['ROLE_USER']);
            $user->setAvatar($user->getAvatar());
            $matriculeExite = $userRepository->findBy(['matricule' =>  $form->getData('matricule')]);
            if ($matriculeExite) {
                $this->addFlash('warning', "Ce matricule est déjà utilisé");
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Mise à jour effectif");

            return $this->redirectToRoute('super_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'titre' => "Mise a jour Information Inspecteur"
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('successwarning', "Suppréssion effectif");
        }

        return $this->redirectToRoute('super_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/college', name: 'user_college_update', methods: ['GET', 'POST'])]
    public function changestatut(
        Request $request,
        EntityManagerInterface $entityManager,
        User $user,
        CollegeRepository $collegeRepository
    ): Response {

        $college = $collegeRepository->find($request->request->all()['college']);


        $user->setCollege($college);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', "College  mise à jour avec succès ");
        return $this->redirect($request->headers->get('referer'));
    }






    #[Route('/{id}/sous_categorie', name: 'user_college_update_sous_categorie', methods: ['GET'])]
    public function updateCategorie(
        College $college,
        UserRepository $userRepository,
        CollegeRepository $collegeRepository
    ): Response {

        return $this->render('super_admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            "college" => $college,
            'titre' => "Liste des Inspecteurs",
            "colleges" => $collegeRepository->findAll()
        ]);
    }
}
