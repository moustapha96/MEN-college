<?php

namespace App\Controller;

use App\Entity\College;
use App\Form\CollegeType;
use App\Repository\CollegeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/college')]
class CollegeController extends AbstractController
{
    #[Route('/', name: 'app_college_index', methods: ['GET'])]
    public function index(CollegeRepository $collegeRepository): Response
    {
        return $this->render('college/index.html.twig', [
            'colleges' => $collegeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_college_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $college = new College();
        $form = $this->createForm(CollegeType::class, $college);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($college);
            $entityManager->flush();

            return $this->redirectToRoute('app_college_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('college/new.html.twig', [
            'college' => $college,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_college_show', methods: ['GET'])]
    public function show(College $college): Response
    {
        return $this->render('college/show.html.twig', [
            'college' => $college,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_college_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, College $college, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollegeType::class, $college);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_college_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('college/edit.html.twig', [
            'college' => $college,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_college_delete', methods: ['POST'])]
    public function delete(Request $request, College $college, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $college->getId(), $request->request->get('_token'))) {
            $entityManager->remove($college);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_college_index', [], Response::HTTP_SEE_OTHER);
    }


}
