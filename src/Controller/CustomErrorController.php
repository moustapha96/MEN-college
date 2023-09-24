<?php


// src/Controller/CustomErrorController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomErrorController extends AbstractController
{
    /**
     * @Route("/404", name="custom_404")
     */
    public function show404(): Response
    {
        // Vous pouvez personnaliser la réponse ici en utilisant le template Twig 404.html.twig
        return $this->render('layouts/404.html.twig');
    }
}
