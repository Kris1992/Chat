<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\{ChatRepository, ParticipantRepository};
use App\Entity\Chat;


//
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Exception\Api\ApiBadRequestHttpException;
use App\Services\Factory\MessageModel\MessageModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Message\MessageFactoryInterface;
use Symfony\Component\WebLink\Link;
use Symfony\Component\Mercure\{PublisherInterface, Update};
use Symfony\Component\Serializer\SerializerInterface;

//przeniesc do facade

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
    public function publicRoom(Chat $chat, ParticipantFactoryInterface $participantFactory, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$chat->hasParticipant($user)) {

            $participant = $participantFactory->create($user, $chat);
            $chat->addParticipant($participant);
            $entityManager->flush();

        }

        return $this->render('chat/public_room.html.twig', [
            'chat' => $chat,
        ]);
    }

    /**
     * @Route("api/chat/hub_url", name="api_hub_url", methods={"GET"})
     */
    public function getHubUrl(Request $request): Response
    {
        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        return new Response(null, Response::HTTP_OK);
    }

            //MessageController>? For now just fast method after I will be refactor this
    /**
     * @Route("api/chat/{id}/message", name="api_chat_message", methods={"POST"})
     */
    public function addMessage(Chat $chat, Request $request, EntityManagerInterface $entityManager, PublisherInterface $publisher, SerializerInterface $serializer, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory, MessageModelFactoryInterface $messageModelFactory, ModelValidatorInterface $modelValidator, MessageFactoryInterface $messageFactory): Response
    {

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        /** @var User $user */
        $user = $this->getUser();
        $messageModel = $messageModelFactory->createFromData($data['content'], $user, $chat);
        $isValid = $modelValidator->isValid($messageModel);
        if ($isValid) {
            $message = $messageFactory->create($messageModel);
            $chat->addMessage($message);
            $entityManager->flush();

            $serializedMessage = $serializer->serialize(
                $message,
                'json', ['groups' => 'chat:message']
            );

            $topics = [
                sprintf('/chat/public/%d', $chat->getId())
            ];

            $othersParticipants = $participantRepository->findAllOthersParticipantsByChat($user, $chat);
            
            if ($othersParticipants) {
                foreach ($othersParticipants as $participant) {
                    $topics[] = sprintf('/%s', $participant->getUser()->getLogin());
                }
            }

            $update = new Update(
                $topics,
                $serializedMessage,
                true
            );
        
            $publisher->__invoke($update);
            
            return new JsonResponse($serializedMessage, Response::HTTP_CREATED); 
        }

        return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, $modelValidator->getErrorMessage());
    }

}
