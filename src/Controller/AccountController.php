<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\{CsrfTokenManagerInterface, CsrfToken};
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Services\Factory\UserModel\UserModelFactoryInterface;
use App\Services\Updater\User\UserUpdaterInterface;
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Security\LoginFormAuthenticator;
use App\Services\Mailer\MailingSystemInterface;
use App\Repository\{UserRepository, PasswordTokenRepository};
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserFormType;
use App\Entity\PasswordToken;
use App\Form\RenewPasswordFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AccountController extends AbstractController
{

    /**
     * @Route("/account/edit", name="account_edit", methods={"POST", "GET"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, UserModelFactoryInterface $userModelFactory, UserUpdaterInterface $userUpdater): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var UserModel $userModel */
        $userModel = $userModelFactory->create($user);
            
        $form = $this->createForm(UserFormType::class, $userModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userUpdater->update($userModel, $user, $form['imageFile']->getData());

            $entityManager->flush();
            $this->addFlash('success', 'Your account is updated!');

            return $this->redirectToRoute('account_edit');
        }

        return $this->render('account/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }

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
     * @Route("/password/change", name="app_change_password", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function changePassword(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $passTokenOld = $user->getPasswordToken();
        if($passTokenOld) {
            $entityManager->remove($passTokenOld);
        }
        $passToken = new PasswordToken($user);
        $user->setPasswordToken($passToken);
        $entityManager->persist($passToken);
        $entityManager->flush();

        return $this->redirectToRoute('app_renew_password', ['token' => $passToken->getToken()]);
    }

    /**
     * @Route("/password/renew/{token}", name="app_renew_password", methods={"POST", "GET"})
     */
    public function renewPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator, PasswordToken $passwordToken): Response
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

    /**
     * @Route("/api/account/delete_image", name="api_delete_account_image", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function deleteImageAction(Request $request, JsonErrorResponseFactory $jsonErrorFactory, ImagesManagerInterface $userImagesManager, EntityManagerInterface $entityManager): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        $userId = $user->getId();

        if($userId === intval($data['id'])) {
            $imageFilename = $user->getImageFilename();
            if(!empty($imageFilename)) {
                $result = $userImagesManager->deleteImage($imageFilename, $user->getLogin());
                if ($result) {
                    $user->setImageFilename(null);
                    $entityManager->flush();
                    return new JsonResponse(null, Response::HTTP_OK);    
                }
            }
        }
        
        return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR, null, 'Image not found.');
    }

    /**
     * @Route("/api/account/update_last_activity", name="api_account_last_activity", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function updateLastActivity(EntityManagerInterface $entityManager): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $user->updateLastActivity();
        $entityManager->flush();        
        
        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/api/account/get_last_activities", name="api_account_get_last_activities", methods={"POST", "GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getLastActivities(Request $request, UserRepository $userRepository): Response
    {
        
        $data = json_decode($request->getContent(), true);

        if($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        $ids = [];

        foreach ($data as $idData) {
            array_push($ids, $idData['id']);    
        }

        $usersActivities = $userRepository->findUsersLastActicity($ids);

        return new JsonResponse($usersActivities, Response::HTTP_OK);
    }

}
