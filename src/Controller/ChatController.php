<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Message\Command\CheckUserActivityOnPublicChat;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\{ChatRepository, ParticipantRepository};
use Symfony\Component\WebLink\Link;
use App\Entity\Chat;

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
    public function publicRoom(Chat $chat, ParticipantFactoryInterface $participantFactory, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$chat->hasParticipant($user)) {

            $participant = $participantFactory->create($user, $chat);
            $chat->addParticipant($participant);
            $entityManager->flush();

            $message = new CheckUserActivityOnPublicChat($participant->getId());
            $envelope = new Envelope($message, [
                new DelayStamp(120000)//2 minutes delay 
            ]);
            $messageBus->dispatch($envelope);

        }

        return $this->render('chat/public_room.html.twig', [
            'chat' => $chat,
        ]);
    }

    /**
     * @Route("/chat/private", name="chat_private", methods={"GET"})
     */
    public function privateList(ChatRepository $chatRepository, Request $request): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        
        $chats = $chatRepository->findPrivateChatsByUser($user);

        return $this->render('chat/private_list.html.twig', [
            'chats' => $chats
        ]);
    }

    /**
     * @Route("api/chat/{id}/update_participant", name="api_chat_update_participant", methods={"POST"})
     */
    public function updateParticipant(Chat $chat, Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $participant = $participantRepository->findParticipantByUserAndChat($user, $chat);
        
        if ($participant) {
            $participant->updateLastSeenAt();
            $entityManager->flush();

            return $this->json(
                $chat,
                200,
                [],
                [   
                    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    AbstractObjectNormalizer::GROUPS => 'chat:participants',
                    AbstractObjectNormalizer::CIRCULAR_REFERENCE_LIMIT => 3,
                ]
            );
        }

        return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, 'Cannot update your last activity. Please refresh this page.');
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

}
