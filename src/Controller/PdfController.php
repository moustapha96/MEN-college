<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\User;
use App\Repository\RapportRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{

    #[Route('/generate-pdf', name: 'app_generate_pdf', methods: ['GET'])]
    public function generatePdf(): Response
    {
        $htmlContent = $this->renderView('pdf/index.html.twig', [
            'controller_name' => 'PdfController',
        ]);

        $pdfContent = shell_exec('wkhtmltopdf - - <<< ' . escapeshellarg($htmlContent));
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="generated.pdf"',
        ]);
    }

    #[Route('/generate-user/{id}', name: 'app_generate_pdf_user', methods: ['GET'])]
    public function generatePdfUser(User $user): Response
    {

        $htmlContent = $this->renderView('pdf/user.html.twig', [
            'controller_name' => 'PdfController',
            'user' => $user
        ]);

        $pdfContent = shell_exec('wkhtmltopdf - - <<< ' . escapeshellarg($htmlContent));
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="editeur.pdf"',
        ]);
    }
    #[Route('/generate-users', name: 'app_generate_pdf_users', methods: ['GET'])]
    public function generatePdfUsers(UserRepository $userRepository): Response
    {

        $users = $userRepository->findAll();
        $htmlContent = $this->renderView('pdf/users.html.twig', [
            'controller_name' => 'PdfController',
            'users' => $users
        ]);

        $pdfContent = shell_exec('wkhtmltopdf - - <<< ' . escapeshellarg($htmlContent));
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="inspecteurs.pdf"',
        ]);
    }
    #[Route('/generate-rapport/{id}', name: 'app_generate_pdf_rapport', methods: ['GET'])]
    public function generatePdfRapport(Rapport $rapport): Response
    {

        $htmlContent = $this->renderView('pdf/rapport.html.twig', [
            'controller_name' => 'PdfController',
            "rapport" => $rapport
        ]);

        $pdfContent = shell_exec('wkhtmltopdf - - <<< ' . escapeshellarg($htmlContent));
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="detail-rapport.pdf"',
        ]);
    }

    #[Route('/generate-rapports', name: 'app_generate_pdf_rapports', methods: ['GET'])]
    public function generatePdfRapports(RapportRepository $rapportRepository): Response
    {

        $rapports = $rapportRepository->findAll();
        $htmlContent = $this->renderView('pdf/rapports.html.twig', [
            'controller_name' => 'PdfController',
            'rapports' => $rapports
        ]);

        $pdfContent = shell_exec('wkhtmltopdf - - <<< ' . escapeshellarg($htmlContent));
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapports.pdf"',
        ]);
    }
}
