<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Form\ConfigurationType;
use App\Repository\ConfigurationRepository;
use App\Services\OrangeSMSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/configuration', name: 'super_admin_')]
class ConfigurationController extends AbstractController
{
    #[Route('/', name: 'configuration_index', methods: ['GET'])]
    public function index(ConfigurationRepository $configurationRepository): Response
    {
        return $this->render('super_admin/configuration/index.html.twig', [
            'configurations' => $configurationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'configuration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $configuration = new Configuration();
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($configuration);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/configuration/new.html.twig', [
            'configuration' => $configuration,
            'form' => $form,
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
        $sendSMS = $request->request->all()['sendSMS'];
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

        $nameSms = $configurationRepository->findOneBy(['cle' => 'sendSMS']);
        $nameSms->setValeur($sendSMS);

        $nameTel = $configurationRepository->findOneBy(['cle' => 'tel']);
        $nameTel->setValeur($tel);


        $entityManager->persist($nameC);
        $entityManager->persist($nameEm);
        $entityManager->persist($nameSms);
        $entityManager->persist($nameTel);

        $entityManager->flush();
        $this->addFlash('success', "Configuration mise à jour avec succès ");
        return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'super_admin_configuration_show', methods: ['GET'])]
    public function show(Configuration $configuration): Response
    {
        return $this->render('super_admin/configuration/show.html.twig', [
            'configuration' => $configuration,
        ]);
    }

    #[Route('/{id}/edit', name: 'configuration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Configuration $configuration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_admin/configuration/edit.html.twig', [
            'configuration' => $configuration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'configuration_delete', methods: ['POST'])]
    public function delete(Request $request, Configuration $configuration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $configuration->getId(), $request->request->get('_token'))) {
            $entityManager->remove($configuration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('super_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
    }
}
