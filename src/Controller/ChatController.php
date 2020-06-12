<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ChatRepository;
use App\Entity\Chat;


//
use App\Exception\Api\ApiBadRequestHttpException;

/**
* @IsGranted("ROLE_USER")
**/
class ChatController extends AbstractController
{
    /**
     * @Route("/chat", name="chat_dashboard", methods={"GET"})
     */
    public function dashboard(): Response
    {

        return $this->render('chat/dashboard.html.twig');
    }

    /**
     * @Route("/chat/public", name="chat_public", methods={"GET"})
     */
    public function publicList(ChatRepository $chatRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('filterValue');
        $chatQuery = $chatRepository->findPublicChatsQuery($searchTerms);

        $pagination = $paginator->paginate(
            $chatQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );

        return $this->render('chat/public_list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/chat/public/{id}", name="chat_public_room", methods={"GET"})
     */
    public function publicRoom(Chat $chat): Response
    {

        return $this->render('chat/public_room.html.twig', [
            'chat' => $chat,
        ]);
    }


            //MessageController>? For now just fast method after I will be refactor this
    /**
     * @Route("api/chat/{id}/message", name="api_chat_message", methods={"POST"})
     */
    public function addMessage(Chat $chat, Request $request): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }
        dump($data);
        return new JsonResponse(null, Response::HTTP_OK); 


    }


}
