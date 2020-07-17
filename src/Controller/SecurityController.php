<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Services\UserRegistration\UserRegistrationInterface;
use App\Services\Checker\CheckerInterface;
use App\Exception\Api\ApiBadRequestHttpException;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserFormType;

class SecurityController extends AbstractController
{
    /**
     * @param   AuthenticationUtils $authenticationUtils
     * @return  Response
     * @Route("/login", name="app_login", methods={"POST", "GET"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('target_path');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @param   Request                     $request
     * @param   GuardAuthenticatorHandler   $guardHandler
     * @param   LoginFormAuthenticator      $formAuthenticator
     * @param   UserRegistrationInterface   $userRegistration
     * @param   EntityManagerInterface      $entityManager
     * @return  Response
     * @Route("/register", name="app_register", methods={"POST", "GET"})
     */
    public function register(Request $request, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator, UserRegistrationInterface $userRegistration, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(UserFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UserModel $userModel */
            $userModel = $form->getData();

            try {
                $user = $userRegistration->register(
                            $request, 
                            $userModel
                        );
            } catch (\Exception $e) {
                return $this->render('security/registration.html.twig', [
                    'userForm' => $form->createView(),
                    'ReCaptchaError' => $e->getMessage()
                ]);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main' // firewall name
            );
        }

        return $this->render('security/registration.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @param   Request                     $request
     * @param   CheckerInterface            $isUserUniqueChecker
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/is_user_unique", name="api_isUserUnique")
     */
    public function isUserUniqueAction(Request $request, CheckerInterface $isUserUniqueChecker, JsonErrorResponseFactory $jsonErrorFactory): Response
    {
        $fieldData = json_decode($request->getContent(), true);
        
        if ($fieldData === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');
        }

        try {
            $isUserUnique = $isUserUniqueChecker->check($fieldData);
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, $e->getMessage());
        }

        if (!$isUserUnique) {
            return $jsonErrorFactory->createResponse(409, JsonErrorResponseTypes::TYPE_CONFLICT_ERROR, null, sprintf('Account with this %s already exist!', $fieldData['fieldName']));
        }

        return new JsonResponse(['is_unique' => true], Response::HTTP_OK);
    }

}
