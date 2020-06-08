<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\{CsrfTokenManagerInterface, CsrfToken};
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\LoginFormAuthenticator;
use App\Services\Mailer\MailingSystemInterface;
use App\Repository\{UserRepository, PasswordTokenRepository};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PasswordToken;
use App\Form\RenewPasswordFormType;

class AccountController extends AbstractController
{

    /**
     * @Route("/password/reset", name="app_reset_password", methods={"POST", "GET"})
     */
    public function resetPassword(Request $request, CsrfTokenManagerInterface $csrfTokenManager, UserRepository $userRepository, MailingSystemInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        if($request->isMethod('POST')) {
            $formData = [
                'email' => $request->request->get('email'),
                'csrf_token' => $request->request->get('_csrf_token')
            ];

            $token = new CsrfToken('authenticate', $formData['csrf_token']);
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException();
            }

            $user = $userRepository->findOneBy(['email' => $formData['email']]);
            
            if (!$user) {
                $this->addFlash('warning', 'E-mail not found in database!');    
            } else {
                $passTokenOld = $user->getPasswordToken();
                if($passTokenOld) {
                    $entityManager->remove($passTokenOld);
                }
                $passToken = new PasswordToken($user);
                $user->setPasswordToken($passToken);
                $entityManager->persist($passToken);
                $entityManager->flush();

                $mailer->sendResetPasswordMessage($user);
                $this->addFlash('success', 'Check your email! We send message to you.');
            }
        }

        return $this->render('account/reset_password.html.twig');
    }

    /**
     * @Route("/password/renew/{token}", name="app_renew_password", methods={"POST", "GET"})
     */
    public function renewPassword($token, Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator, PasswordToken $passwordToken): Response
    {

        if(!$passwordToken || $passwordToken->isExpired()) {
            $this->addFlash('warning', 'Reset password token not exist or expired!');

            return $this->redirectToRoute('app_reset_password');
        }
        
        $form = $this->createForm(RenewPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var RenewPasswordModel */
            $passwordModel = $form->getData();
            $user = $passwordToken->getUser();

            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $passwordModel->getPlainPassword()
            ));

            $user->setPasswordToken(null);
            $user->resetFailedAttempts();
            //$em->flush();
            //$em->remove($passwordToken);
            $entityManager->flush();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main' // firewall name
            );
        }

        return $this->render('account/renew_password.html.twig', [
            'renewPasswordForm' => $form->createView(),
        ]);
    }

}
