<?php

namespace App\Controller;

use App\Entity\SousCategorie;
use App\Form\SousCategorieType;
use App\Repository\CollegeRepository;
use App\Repository\SousCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sous_colleges')]
class SousCategorieController extends AbstractController
{
    #[Route('/', name: 'admin_sous_categorie_index', methods: ['GET'])]
    public function index(SousCategorieRepository $sousCategorieRepository): Response
    {
        return $this->render('admin/sous_categorie/index.html.twig', [
            'sous_categories' => $sousCategorieRepository->findAll(),
            'titre' => "Liste des Sous Colleges"
        ]);
    }




    #[Route('/create', name: 'admin_sous_categorie_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        CollegeRepository $collegeRepository
    ): Response {

        $sousCategorie = new SousCategorie();
        $collegeID = $request->request->get('college');
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');


        if ($request->isMethod('POST') && $collegeRepository->find($collegeID && $nom != '' && $description != "")) {

            $sousCategorie->setCollege($collegeRepository->find($collegeID));
            $sousCategorie->setNom($nom);
            $sousCategorie->setDescription($description);

            $entityManager->persist($sousCategorie);
            $entityManager->flush();

            $this->addFlash('success', "Sous collège ajouté avec succès");
            return $this->redirectToRoute('admin_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash('warning', "Sous collège non ajouté ");
        return $this->redirectToRoute('admin_sous_categorie_new', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/new', name: 'admin_sous_categorie_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CollegeRepository $collegeRepository
    ): Response {

        return $this->render('admin/sous_categorie/new.html.twig', [
            'colleges' => $collegeRepository->findAll(),
            'titre' => "Nouveau Sous Colleges"
        ]);
    }


    #[Route('/{id}', name: 'admin_sous_categorie_show', methods: ['GET'])]
    public function show(SousCategorie $sousCategorie): Response
    {
        return $this->render('admin/sous_categorie/show.html.twig', [
            'sous_categorie' => $sousCategorie,
            'titre' => "Détails Sous Catégorie "
        ]);
    }


    #[Route('/{id}/edit', name: 'admin_sous_categorie_edit', methods: ['GET'])]
    public function edit(
        Request $request,
        SousCategorie $sousCategorie,
        EntityManagerInterface $entityManager,
        CollegeRepository $collegeRepository
    ): Response {

        return $this->render('admin/sous_categorie/edit.html.twig', [
            'sous_categorie' => $sousCategorie,
            'colleges' => $collegeRepository->findAll(),
            'titre' => "Editer Sous Colleges"
        ]);
    }




    #[Route('/{id}/update', name: 'admin_sous_categorie_update', methods: ['POST'])]
    public function update(
        Request $request,
        SousCategorie $sousCategorie,
        EntityManagerInterface $entityManager,
        CollegeRepository $collegeRepository
    ): Response {

        $collegeID = $request->request->get('college');
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');


        if ($request->isMethod('POST') && $nom != '' && $description != "") {

            $sousCategorie->setCollege($collegeRepository->find($collegeID));
            $sousCategorie->setNom($nom);
            $sousCategorie->setDescription($description);

            $entityManager->persist($sousCategorie);
            $entityManager->flush();

            $this->addFlash('success', "Sous collège mise à jour avec succès");
            return $this->redirectToRoute('admin_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('warning', "Sous collège non mise à jour ");
        return $this->redirectToRoute('admin_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'admin_sous_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, SousCategorie $sousCategorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sousCategorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sousCategorie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
