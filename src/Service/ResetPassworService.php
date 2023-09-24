<?php

namespace App\Service;

use App\Entity\Emprunt;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Repository\UserRepository;
use App\Service\ConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPassworService extends AbstractController
{


    use ResetPasswordControllerTrait;
    private $config;
    private $resetPasswordHelper;
    private $entityManager;
    private $tokenGenerator;
    private $mailer;
    private $translator;
    private $userPasswordHasher;

    public function __construct(
        ConfigurationService $config,
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $userPasswordHasher,
        // TokenGeneratorInterface $tokenGenerator
    ) {
        $this->config = $config;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
        // $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @param string $password
     * @param string $token
     * @throws EntityNotFoundException
     */
    public function newPassword(string $password, string $token)
    {

        if (!$token) {
            throw new EntityNotFoundException("token invalide");
        }
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        if (!$user) {
            throw new EntityNotFoundException("utilisateur avec ce token " . $token . " n'esxite pas ");
        }
        $user->setResetToken(null);
        $encodedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($encodedPassword);
        $this->entityManager->flush();
    }


    /**
     * @param string $password
     * @param string $token
     * @throws EntityNotFoundException
     */
    public function reset(string $password, string $token = null)
    {
        if ($token) {
            $this->storeTokenInSession($token);
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw new  EntityNotFoundException("Aucun jeton de réinitialisation du mot de passe trouvé dans l'URL ou dans la session.");
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $error =  sprintf(
                '%s - %s',
                $this->translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $this->translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            );
            $this->addFlash('reset_password_error', $error);

            return $this->redirectToRoute('app_check_email');
        }

        $this->resetPasswordHelper->removeResetRequest($token);

        $encodedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($encodedPassword);
        $this->entityManager->flush();

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();
    }


    /**
     * @param string $emailFormData
     * @param string $uri
     * @throws EntityNotFoundException
     */
    public function processSendingPasswordResetEmail(string $emailFormData, string $uri)
    {

        $emailbase = $this->config->get("email");
        $nombase =  $this->config->get("name");
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        if (!$user) {
            return
                $this->redirectToRoute('app_check_email');
        }

        try {
            $tokenG = $this->resetPasswordHelper->generateResetToken($user);
            dd($tokenG);
            $user->setResetToken($tokenG->getToken());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            try {
                $url = $uri . $tokenG->getToken();
                $email = (new TemplatedEmail())
                    ->from(new Address($emailbase, $nombase))
                    ->to($user->getEmail())
                    ->subject('Votre demande de réinitialisation de mot de passe')
                    ->htmlTemplate('reset_password/emailTokenApi.html.twig')
                    ->context([
                        'url' => $url,
                        'resetToken' => $tokenG,
                    ]);
                $this->mailer->send($email);

                $this->addFlash('success', "Demande envoyé, Merci de vérifier votre boite mail ");
                return $this->redirectToRoute('app_check_email');
            } catch (\Throwable $th) {
                $this->addFlash('danger', 'Demande non envoyé, Merci de réessayer plus tard ');
                return $this->redirectToRoute('app_check_email');
            }
        } catch (ResetPasswordExceptionInterface $e) {

            $time = $this->resetPasswordHelper->getTokenLifetime();
            $error = "Veuillez vérifier votre boite mail ou \n Réessayer dans  " . $time . " secondes";
            $this->addFlash('warning', $error);

            return $this->redirectToRoute('app_check_email');
        }



        return new JsonResponse(['success' => true]);
    }
}
