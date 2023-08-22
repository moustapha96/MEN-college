<?php

namespace App\Controller;

use App\Entity\Rapport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlobController extends AbstractController
{
    #[Route('/blob/{id}', name: 'app_view_blob')]
    public function index($id, EntityManagerInterface $entityManager): Response
    {

        // Récupérez votre entité à partir de l'ID (assurez-vous de la récupérer correctement depuis votre base de données)
        $entity = $entityManager->getRepository(Rapport::class)->find($id);

        // Récupérez les données binaires du champ resultatFichier
        $binaryData = $entity->getResultatFichier();

        // Convertissez les données binaires en une chaîne
        $binaryString = stream_get_contents($binaryData);

        // Créez une réponse avec la chaîne convertie et le type MIME approprié
        $response = new Response($binaryString);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
