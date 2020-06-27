<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\{Response, Request};
use App\Repository\{FriendRepository, UserRepository};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
* @IsGranted("ROLE_USER")
**/
class FriendController extends AbstractController
{

    /**
     * @Route("/friend", name="friend_list", methods={"GET"})
     */
    public function list(FriendRepository $friendRepository, PaginatorInterface $paginator, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $searchTerms = $request->query->getAlnum('filterValue');
        $friendQuery = $friendRepository->findAllQueryByStatus($searchTerms, $currentUser, 'Accepted');

        $pagination = $paginator->paginate(
            $friendQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 6)/*limit per page*/
        );

        $pagination->setCustomParameters([
            'placeholder' => 'Search in friends...'
        ]);
    
        return $this->render('friend/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/friend/search", name="friend_search", methods={"GET"})
     */
    public function search(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, FriendRepository $friendRepository): Response
    {   
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $searchTerms = $request->query->getAlnum('filterValue');
        $userQuery = $userRepository->findAllQuery($searchTerms, $currentUser);

        $pagination = $paginator->paginate(
            $userQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 6)/*limit per page*/
        );

        $pagination->setCustomParameters([
            'placeholder' => 'Enter here e-mail or login...'
        ]);

        return $this->render('friend/search.html.twig', [
            'pagination' => $pagination,
        ]);
    }

}
