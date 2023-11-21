<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/super_admin',  name: "super_admin_")]
#[IsGranted("ROLE_SUPER_ADMIN", statusCode: 404, message: "Page non accéssible")]
class FichierController extends AbstractController
{
    #[Route('/fichier_word', name: 'fichier_word')]
    public function pdf(): Response
    {

        $directory = $this->getParameter('synthese_rapports');
        $files = array_filter(scandir($directory), function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), ['docx']);
        });
        return $this->render('super_admin/fichier/index_word.html.twig', [
            'titre' => 'Liste des fichiers word générer',
            'files' => $files,
        ]);
    }
    #[Route('/fichier_pdf', name: 'fichier_pdf')]
    public function word(): Response
    {

        $directory = $this->getParameter('pdf_directory');
        $files = array_filter(scandir($directory), function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), ['pdf']);
        });

        return $this->render('super_admin/fichier/index_pdf.html.twig', [
            'titre' => 'Liste des fichiers pdf télécharger',
            'files' => $files,
        ]);
    }

    #[Route('/fichier_pdf/{name}/suppression', name: 'fichier_pdf_delete')]
    public function deleteFilePDF(string $name): Response
    {
        $directory = $this->getParameter('pdf_directory');

        $filepath = $directory . $name;
        if (!file_exists($filepath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        $filesystem = new Filesystem();
        $filesystem->remove($filepath);

        $this->addFlash("Suppression fichier", "Fichier supprimer avec succés");
        return $this->redirectToRoute('super_admin_fichier_pdf');
    }

    #[Route('/fichier_word/{name}/suppression', name: 'fichier_word_delete')]
    public function deleteFile(string $name): Response
    {
        $directory = $this->getParameter('synthese_rapports');
        $filepath = $directory . $name;
        if (!file_exists($filepath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }
        $filesystem = new Filesystem();
        $filesystem->remove($filepath);

        $this->addFlash("Suppression fichier", "Fichier supprimer avec succés");
        return $this->redirectToRoute('super_admin_fichier_word');
    }
}
