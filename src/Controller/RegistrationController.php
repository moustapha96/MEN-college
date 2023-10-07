<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;



class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private MailerService $mailerService;

    public function __construct(EmailVerifier $emailVerifier, MailerService $mailerService,)
    {
        $this->emailVerifier = $emailVerifier;
        $this->mailerService = $mailerService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,

    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $referer = $request->headers->get('referer');

        $data = $request->request->all();

        if ($request->isMethod('POST')) {
            $referer = $request->headers->get('referer');

            $userByEmail = $userRepository->findOneBy(['email' => $data['email']]);
            if ($userByEmail) {
                $this->addFlash('danger', 'Cette adresse email existe déjà.');
                return $this->redirect($referer);
            }

            $userByMatricule = $userRepository->findOneBy(['matricule' => $data['matricule']]);
            if ($userByMatricule) {
                $this->addFlash('danger', 'Ce matricule existe déjà.');
                return $this->redirect($referer);
            }

            $userByPhone = $userRepository->findOneBy(['phone' => $data['phone']]);
            if ($userByPhone) {
                $this->addFlash('danger', 'Ce numéro de téléphone existe déjà.');
                return $this->redirect($referer);
            }
            if ($data['plainPassword'] != $data['confirPassword']) {
                $this->addFlash('danger', 'Les mots de passes ne correspondent pas ');
                return $this->redirect($referer);
            }

            if ($data['plainPassword'] != $data['confirPassword']) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                return $this->redirect($referer);
            } else {

                $user->setEnabled(false);
                $user->setStatus("ACTIVE");
                $user->setIsActiveNow(true);
                $user->setAvatar('avatar.png');
                $user->setRoles(['ROLE_USER']);
                $user->setPhone($data['phone']);
                $user->setFirstName($data['firstName']);
                $user->setLastName($data['lastName']);
                $user->setAdresse($data['adresse']);
                $user->setSexe($data['sexe']);
                $user->setMatricule($data['matricule']);
                $user->setEmail($data['email']);

                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $data['plainPassword']
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();


                $this->mailerService->sendMailCompteCreer(
                    $user,
                    $data['plainPassword']
                );


                // $this->emailVerifier->sendEmailConfirmation(
                //     'app_verify_email',
                //     $user,
                //     (new TemplatedEmail())
                //         ->from(new Address('men-rapport@gmail.com', 'MEN'))
                //         ->cc(new Address('khouma964@gmail.com', 'MEN'))
                //         ->to($user->getEmail())
                //         ->subject('Merci de confirmer cotre email')
                //         ->htmlTemplate('registration/confirmation_email.html.twig')
                // );
                $this->addFlash('success', 'Inscription réussie! Veuillez vérifier votre email.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            // 'form' => $form->createView(),
        ]);
    }





    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
