<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Annotation\Route;
use App\Services\Factory\UserModel\UserModelFactoryInterface;
use App\Services\Updater\User\UserUpdaterInterface;
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
     * @Route("/admin/account/{id}/edit", name="admin_account_edit", methods={"POST", "GET"})
     */
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserModelFactoryInterface $userModelFactory, UserUpdaterInterface $userUpdater)
    {
        /** @var UserModel $userModel */
        $userModel = $userModelFactory->create($user);
          
        $form = $this->createForm(UserFormType::class, $userModel, [
            'is_admin' => true
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $user = $userUpdater->update($userModel, $user, null);
            
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
     * @Route("/admin/account/ban_selected", name="admin_account_ban_selected",  methods={"POST"})
     */
    public function banSelected(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository)
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

}
