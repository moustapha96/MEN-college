<?php

namespace App\Controller;

use App\Form\ConfigurationType;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/configuration', name: 'super_admin_')]
#[IsGranted("ROLE_SUPER_ADMIN", statusCode: 404, message: "Page non accéssible")]
class ConfigurationController extends AbstractController
{
    #[Route('/', name: 'configuration_index', methods: ['GET'])]
    public function index(ConfigurationRepository $configurationRepository): Response
    {
        return $this->render('super_admin/configuration/index.html.twig', [
            'configurations' => $configurationRepository->findAll(),
        ]);
    }




    #[Route('/mise-a-jour', name: 'configuration_update', methods: ['POST'])]
    public function updateData(
        Request $request,
        ConfigurationRepository $configurationRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $name = $request->request->all()['name'];
        $email = $request->request->all()['email'];
        $tel = $request->request->all()['tel'];

        if (trim($name) == '' ||  !$name) {
            $this->addFlash('warning', "Nom de l'application ne peut etre null");
            return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($email == '' ||  !$email) {
            $this->addFlash('warning', "Email de l'application ne peut etre null");
            return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }

        $nameC = $configurationRepository->findOneBy(['cle' => 'name']);
        $nameC->setValeur(trim($name));

        $nameEm = $configurationRepository->findOneBy(['cle' => 'email']);
        $nameEm->setValeur($email);

        $nameTel = $configurationRepository->findOneBy(['cle' => 'tel']);
        $nameTel->setValeur($tel);


        $entityManager->persist($nameC);
        $entityManager->persist($nameEm);
        $entityManager->persist($nameTel);

        $entityManager->flush();
        $this->addFlash('success', "Configuration mise à jour avec succès ");
        return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/upload-logo-tow', name: 'upload_logo_two', methods: ['POST'])]
    public function uploadLogo(
        Request $request,
        ConfigurationRepository $configurationRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $uploadedFile = $request->files->get('logo');

        if ($uploadedFile) {

            $destination = $this->getParameter('kernel.project_dir') . '/public/images';
            $uploadedFile->move($destination, $uploadedFile->getClientOriginalName());
            $nameLogo2 = $configurationRepository->findOneBy(['cle' => 'logo2']);
            $nameLogo2->setValeur($uploadedFile->getClientOriginalName());
            $entityManager->persist($nameLogo2);
            $entityManager->flush();
        }
        return $this->redirectToRoute('super_admin_configuration_index');
    }



    #[Route('/upload-logo-one', name: 'upload_logo_one', methods: ['POST'])]
    public function uploadLogo1(
        Request $request,
        ConfigurationRepository $configurationRepository,
        EntityManagerInterface $entityManager,
    ): Response {


        $uploadedFile = $request->files->get('logo');

        if ($uploadedFile) {
            $destination = $this->getParameter('kernel.project_dir') . '/public/images';
            $uploadedFile->move($destination, $uploadedFile->getClientOriginalName());
            $nameLogo1 = $configurationRepository->findOneBy(['cle' => 'logo1']);
            $nameLogo1->setValeur($uploadedFile->getClientOriginalName());
            $entityManager->persist($nameLogo1);
            $entityManager->flush();
        }
        return $this->redirectToRoute('super_admin_configuration_index');
    }
}
