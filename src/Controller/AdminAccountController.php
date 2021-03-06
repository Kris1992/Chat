<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\Factory\UserModel\UserModelFactoryInterface;
use App\Services\Updater\User\UserUpdaterInterface;
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\BanManager\BanManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\UserRepository;
use App\Form\UserFormType;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @IsGranted("ROLE_ADMIN")
**/
class AdminAccountController extends AbstractController
{
    /**
     * @param   UserRepository      $userRepository
     * @param   PaginatorInterface  $paginator
     * @param   Request             $request
     * @return  Response
     * @Route("/admin/account", name="admin_account", methods={"GET"})
     */
    public function list(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('filterValue');
        $userQuery = $userRepository->findAllQuery($searchTerms);

        $pagination = $paginator->paginate(
            $userQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );
        
        return $this->render('admin_account/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @param   User                        $user
     * @param   Request                     $request
     * @param   EntityManagerInterface      $entityManager
     * @param   UserModelFactoryInterface   $userModelFactory
     * @param   UserUpdaterInterface        $userUpdater
     * @return  Response
     * @Route("/admin/account/{id}/edit", name="admin_account_edit", methods={"POST", "GET"})
     */
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserModelFactoryInterface $userModelFactory, UserUpdaterInterface $userUpdater): Response
    {
        /** @var UserModel $userModel */
        $userModel = $userModelFactory->create($user);
          
        $form = $this->createForm(UserFormType::class, $userModel, [
            'is_admin' => true
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $user = $userUpdater->update($userModel, $user, $form['imageFile']->getData());
            
            $entityManager->flush();
            $this->addFlash('success', 'User is updated!');

            return $this->redirectToRoute('admin_account_edit', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('admin_account/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }

    /**
     * @param   Request                 $request
     * @param   User                    $user
     * @param   EntityManagerInterface  $entityManager
     * @param   BanManagerInterface     $banManager
     * @return  Response
     * @Route("/admin/account/{id}/ban", name="admin_account_ban",  methods={"POST"})
     */
    public function banManage(Request $request, User $user, EntityManagerInterface $entityManager, BanManagerInterface $banManager): Response
    {
        $option = json_decode($request->getContent(), true);

        try {
            if ($option !== null && $option !== '') {
                $user = $banManager->ban($user, intval($option));
            } else {
                $user = $banManager->unBan($user);
            }
        } catch (\Exception $e) {
            return new Response(null, Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
        $entityManager->flush();

        return new Response();
    }

    /**
     * @param   Request                 $request
     * @param   EntityManagerInterface  $entityManager
     * @param   UserRepository          $userRepository
     * @return  Response
     * @Route("/admin/account/ban_selected", name="admin_account_ban_selected",  methods={"POST"})
     */
    public function banSelected(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $submittedToken = $request->request->get('token');
        if($request->request->has('banId')) {
            if ($this->isCsrfTokenValid('ban_multiple', $submittedToken)) {
                $ids = $request->request->get('banId');
                $users = $userRepository->findAllByIds($ids);

                if($users) {
                    foreach ($users as $user) {
                        if (!$user->isAdmin()) {
                            $user->setBanTo(new \DateTime('now +7 days'));
                        }
                    }
                    $entityManager->flush();

                    $this->addFlash('success','Users are banned!');
                    return $this->redirectToRoute('admin_account');
                }

            } else {
                $this->addFlash('danger','Wrong token.');
                return $this->redirectToRoute('admin_account');
            }
        }

        $this->addFlash('warning','Nothing to do.');
        return $this->redirectToRoute('admin_account');
    }

    /**
     * @param   Request                     $request
     * @param   User                        $user
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @param   ImagesManagerInterface      $userImagesManager
     * @param   EntityManagerInterface      $entityManager
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/admin/account/{id}/delete_image", name="api_admin_delete_account_image",
     * methods={"DELETE"})
     */
    public function deleteUserImageAction(Request $request, User $user, JsonErrorResponseFactory $jsonErrorFactory, ImagesManagerInterface $userImagesManager, EntityManagerInterface $entityManager): Response
    {

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }
        
        //double check that everything is ok
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

}
