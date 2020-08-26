<?php declare(strict_types=1);

namespace App\Controller;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\Factory\FriendInvitation\FriendInvitationFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use App\Services\Updater\Friend\FriendUpdaterInterface;
use App\Repository\{FriendRepository, UserRepository};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{User, Friend};

/**
* @IsGranted("ROLE_USER")
**/
class FriendController extends AbstractController
{

    /**
     * @param   FriendRepository    $friendRepository
     * @param   PaginatorInterface  $paginator
     * @param   Request             $request
     * @return  Response
     * @Route("/friend", name="friend_list", methods={"GET"})
     */
    public function list(FriendRepository $friendRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $searchTerms = $request->query->getAlnum('filterValue');
        $friendQuery = $friendRepository->findAllQueryByStatus(
            $searchTerms, 
            $this->getUser(), 
            'Accepted'
        );

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
     * @param   UserRepository      $userRepository
     * @param   PaginatorInterface  $paginator
     * @param   Request             $request
     * @param   FriendRepository    $friendRepository
     * @return  Response
     * @Route("/friend/search", name="friend_search", methods={"GET"})
     */
    public function search(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, FriendRepository $friendRepository): Response
    {   
        
        $searchTerms = $request->query->getAlnum('filterValue');
        $userQuery = $userRepository->findAllQuery($searchTerms, false);

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

    /**
     * @param   FriendRepository $friendRepository
     * @return  Response
     * @Route("/friend/requests", name="friend_requests", methods={"GET"})
     */
    public function requestslist(FriendRepository $friendRepository): Response
    {
        
        $friendRequests = $friendRepository->findAllToAccept($this->getUser());
    
        return $this->render('friend/requests_list.html.twig', [
            'friendRequests' => $friendRequests
        ]);
    }


    //Api
    /**
     * @param   User                                $user
     * @param   JsonErrorResponseFactory            $jsonErrorFactory
     * @param   FriendInvitationFactoryInterface    $friendInvitationFactory
     * @param   EntityManagerInterface              $entityManager
     * @param   FriendRepository                    $friendRepository
     * @return  Response
     * @Route("/api/friend/user/{id}/invite", name="api_friend_invite", methods={"GET"})
     */
    public function inviteAction(User $user, JsonErrorResponseFactory $jsonErrorFactory, FriendInvitationFactoryInterface $friendInvitationFactory, EntityManagerInterface $entityManager, FriendRepository $friendRepository): Response
    {   

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($user !== $currentUser) {
            //check is history of this realationship (I want keep just one friend object per pair)
            $oldStatus = $friendRepository->findAllBetweenUsers($currentUser, $user);
            if ($oldStatus) {
                $entityManager->remove($oldStatus);
            }

            $friend = $friendInvitationFactory->create($currentUser, $user);
            $entityManager->persist($friend);
            $entityManager->flush();
            return new JsonResponse(null, Response::HTTP_OK);
        }

        return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, 'Something goes wrong.');
    }

    /**
     * @param   Friend                      $friend
     * @param   Request                     $request
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @param   FriendUpdaterInterface      $friendUpdater
     * @param   EntityManagerInterface      $entityManager
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/friend/{id}/response", name="api_friend_response", methods={"POST", "GET"})
     */
    public function responseAction(Friend $friend, Request $request, JsonErrorResponseFactory $jsonErrorFactory, FriendUpdaterInterface $friendUpdater, EntityManagerInterface $entityManager): Response
    {   
        $data = json_decode($request->getContent(), true);

        if($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        if ($friend->getInvitee() === $this->getUser()) {
            $friend = $friendUpdater->update($friend, $data['status']);
            $entityManager->flush();
            return new JsonResponse(null, Response::HTTP_OK);
        }

        return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, 'Something goes wrong.');
    }

    /**
     * @param   FriendRepository $friendRepository
     * @return  Response
     * @Route("/api/friend", name="api_get_friend", methods={"GET"})
     */
    public function getFriendsAction(FriendRepository $friendRepository): Response
    {  

        $friends = $friendRepository->findAllByStatus($this->getUser(), 'Accepted');
        
        return $this->json(
            $friends,
            200,
            [],
            [
                AbstractObjectNormalizer::GROUPS => 'chat:friends',
                AbstractObjectNormalizer::CIRCULAR_REFERENCE_LIMIT => 2,
            ]
        );

    }

}
