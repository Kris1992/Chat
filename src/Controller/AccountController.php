<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\{UserRepository, PasswordTokenRepository, FriendRepository};
use App\Services\PasswordTokenGenerator\PasswordTokenGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Services\Factory\UserModel\UserModelFactoryInterface;
use App\Services\Updater\User\UserUpdaterInterface;
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\PasswordReseter\PasswordReseterInterface;
use App\Security\LoginFormAuthenticator;
use App\Form\{UserFormType, RenewPasswordFormType};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{User, PasswordToken};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AccountController extends AbstractController
{

    /**
     * @param   Request                       $request
     * @param   EntityManagerInterface        $entityManager
     * @param   UserModelFactoryInterface     $userModelFactory
     * @param   UserUpdaterInterface          $userUpdater
     * @return  Response
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
     * @param   User        $user
     * @return  Response
     * @Route("/account/{id}", name="account_profile", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function profile(User $user): Response
    {

        return $this->render('account/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @param   Request                     $request
     * @param   PasswordReseterInterface    $passwordReseter
     * @return  Response
     * @Route("/password/reset", name="app_reset_password", methods={"POST", "GET"})
     */
    public function resetPassword(Request $request, PasswordReseterInterface $passwordReseter): Response
    {

        if($request->isMethod('POST')) {

            try {
                $passwordReseter->reset(
                    $request->request->get('email'), 
                    $request->request->get('_csrf_token')
                );

                $this->addFlash('success', 'Check your email! We send message to you.');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
            }

        }

        return $this->render('account/reset_password.html.twig');
    }

    /**
     * @param   PasswordTokenGeneratorInterface $passwordTokenGenerator
     * @return  Response
     * @Route("/password/change", name="app_change_password", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function changePassword(PasswordTokenGeneratorInterface $passwordTokenGenerator): Response
    {

        $passToken = $passwordTokenGenerator->generate($this->getUser());

        return $this->redirectToRoute('app_renew_password', ['token' => $passToken->getToken()]);
    }

    /**
     * @param   Request                         $request
     * @param   UserPasswordEncoderInterface    $passwordEncoder
     * @param   EntityManagerInterface          $entityManager
     * @param   GuardAuthenticatorHandler       $guardHandler
     * @param   LoginFormAuthenticator          $formAuthenticator
     * @param   PasswordToken                   $passwordToken
     * @return  Response
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
     * @param   Request                     $request
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @param   ImagesManagerInterface      $userImagesManager
     * @param   EntityManagerInterface      $entityManager
     * @return  Response
     * @throws  ApiBadRequestHttpException
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

        if($user->getId() === intval($data['id'])) {
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
     * @param   EntityManagerInterface  $entityManager
     * @param   FriendRepository        $friendRepository
     * @return  Response
     * @Route("/api/account/update_last_activity", name="api_account_last_activity", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function updateLastActivityAction(EntityManagerInterface $entityManager, FriendRepository $friendRepository): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $user->updateLastActivity();
        $entityManager->flush();

        $friendPendingsCount = $friendRepository->countInvitationsByUser($user);
        
        return new JsonResponse(['pendingInvites' => $friendPendingsCount], Response::HTTP_OK);
    }

    /**
     * @param   Request         $request
     * @param   UserRepository  $userRepository
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/account/get_last_activities", name="api_account_get_last_activities", methods={"POST", "GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getLastActivitiesAction(Request $request, UserRepository $userRepository): Response
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
