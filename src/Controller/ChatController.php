<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request, ResponseHeaderBag};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use App\Repository\{ChatRepository, ParticipantRepository, UserRepository};
use App\Message\Command\CheckUserActivityOnPublicChat;
use App\Services\ChatCreatorSystem\ChatCreatorSystemInterface;
use App\Services\ParticipantSystem\ParticipantSystemInterface;
use App\Services\ChatPrinterSystem\ChatPrinterSystemInterface;
use App\Services\Factory\ChatModel\ChatModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Chat\ChatFactoryInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\WebLink\Link;
use App\Entity\{Chat, User};

/**
* @IsGranted("ROLE_USER")
* @IsGranted("ENTER_SITE")
**/
class ChatController extends AbstractController
{
    /**
     * @return  Response
     * @Route("/chat", name="chat_dashboard", methods={"GET"})
     */
    public function dashboard(): Response
    {

        return $this->render('chat/dashboard.html.twig');
    }

    /**
     * @param   ChatRepository      $chatRepository
     * @param   PaginatorInterface  $paginator
     * @param   Request             $request
     * @return  Response
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
     * @param   Chat                            $chat
     * @param   ParticipantFactoryInterface     $participantFactory
     * @param   EntityManagerInterface          $entityManager
     * @param   MessageBusInterface             $messageBus
     * @return  Response
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
     * @param   ChatRepository $chatRepository
     * @return  Response
     * @Route("/chat/private", name="chat_private", methods={"GET"})
     */
    public function privateList(ChatRepository $chatRepository): Response
    {
        
        $chats = $chatRepository->findPrivateChatsByUser($this->getUser(), 0);

        return $this->render('chat/private_list.html.twig', [
            'chats' => $chats
        ]);
    }

    /**
     * @param   Request                         $request
     * @param   ChatCreatorSystemInterface      $chatCreatorSystem
     * @return  Response
     * @Route("/chat/private/create", name="chat_private_create", methods={"POST"})
     */
    public function createPrivateRoom(Request $request, ChatCreatorSystemInterface $chatCreatorSystem): Response
    {

        $submittedToken = $request->request->get('token');

        try {
            if (
                !$request->request->has('friends')  || 
                !$this->isCsrfTokenValid('private_chat', $submittedToken)
            ) {
                throw new \Exception("Cannot create this chat room.");
            }

            $chatCreatorSystem->create($this->getUser(), $request->request->get('friends'));
            $this->addFlash('success','Chat was created.');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('chat_private');
    }

    /**
     * @param   int                             $chatUserId
     * @param   ChatCreatorSystemInterface      $chatCreatorSystem
     * @return  Response
     * @Route("/chat/private/create/user/{id}", name="chat_private_create_with_user", methods={"GET"})
     */
    public function createPrivateRoomWithUser(int $id, ChatCreatorSystemInterface $chatCreatorSystem): Response
    {

        try {

            $chatCreatorSystem->create($this->getUser(), [$id]);
            $this->addFlash('success','Chat was created.');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('chat_private');
    }

    /**
     * @param   Request             $request
     * @param   ChatRepository      $chatRepository
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/chat/private", name="api_get_chats", methods={"POST"})
     */
    public function getChatsAction(Request $request, ChatRepository $chatRepository): Response
    {   

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        $chats = $chatRepository->findPrivateChatsByUser($this->getUser(), $data['offset']);

        return $this->json(
                $chats,
                200,
                [],
                [
                    AbstractObjectNormalizer::GROUPS => [
                        'chat:list',
                        'chat:participants'
                    ],
                ]
            );

    }
    
    /**
     * @param   Chat                        $chat
     * @param   EntityManagerInterface      $entityManager
     * @param   ParticipantRepository       $participantRepository
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @return  Response
     * @Route("/api/chat/{id}/update_participant", name="api_chat_update_participant", methods={"POST"})
     */
    public function updateParticipantAction(Chat $chat, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        $participant = $participantRepository->findParticipantByUserAndChat(
            $this->getUser(), 
            $chat
        );
        
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
     * @param   Chat                        $chat
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @return  Response
     * @Route("/api/chat/{id}/other_participants", name="api_chat_get_other__participants", methods={"GET"})
     */
    public function getParticipantsAction(Chat $chat, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        $participants = $chat->getOtherParticipants($this->getUser());

        if ($participants) {

            return $this->json(
                $participants,
                200,
                [],
                [   
                    AbstractObjectNormalizer::GROUPS => 'chat:participants',
                ]
            );
        }

        return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, 'Cannot get list of participants. Please refresh this page.');
    }

    /**
     * @param   Request     $request
     * @return  Response
     * @Route("/api/chat/hub_url", name="api_hub_url", methods={"GET"})
     */
    public function getHubUrlAction(Request $request): Response
    {
        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        return new Response(null, Response::HTTP_OK);
    }

    /**
     * @param   Chat                            $chat
     * @param   Request                         $request
     * @param   ChatPrinterSystemInterface      $chatPrinterSystem
     * @param   JsonErrorResponseFactory        $jsonErrorFactory
     * @throws  ApiBadRequestHttpException
     * @return  Response
     * @Route("/api/chat/{id}/file", name="api_get_chat_data_file", methods={"POST"})
     */
    public function getChatDataAsFileAction(Chat $chat, Request $request, ChatPrinterSystemInterface $chatPrinterSystem, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        $this->denyAccessUnlessGranted('CHAT_VIEW', $chat);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        try {

            $link = $chatPrinterSystem->printToFile(
                $chat,
                $this->getUser(),
                new \DateTime($data['startAt']),
                new \DateTime($data['stopAt']), 
                $data['fileFormat']
            );

        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR, null, $e->getMessage());
        }

        return new JsonResponse($link, Response::HTTP_OK);  
    }

                                            //participant
    /**
     * @param   Chat                            $chat
     * @param   Request                         $request
     * @param   ParticipantSystemInterface      $participantSystem
     * @param   EntityManagerInterface          $entityManager
     * @return  Response
     * @Route("/chat/{id}/participant", name="chat_participant_add", methods={"POST"})
     */
    public function addParticipant(Chat $chat, Request $request, ParticipantSystemInterface $participantSystem, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('CHAT_MANAGE', $chat);

        $submittedToken = $request->request->get('token');

        if($request->request->has('friends')) {
            if ($this->isCsrfTokenValid('private_chat_participant', $submittedToken)) {
                try {
                    $chat = $participantSystem->add($chat, $request->request->get('friends'));
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('chat_private');
                }

                $this->addFlash('success','Participants were added to chat.');
                return $this->redirectToRoute('chat_private');
            }
        }

        $this->addFlash('danger','Adding participants to the chat room fails.');
        return $this->redirectToRoute('chat_private');
    }

    /**
     * @param   Chat                            $chat
     * @param   Request                         $request
     * @param   ParticipantSystemInterface      $participantSystem
     * @param   EntityManagerInterface          $entityManager
     * @return  Response
     * @Route("/chat/{id}/participant/remove", name="chat_participant_remove", methods={"POST"})
     */
    public function removeParticipant(Chat $chat, Request $request, ParticipantSystemInterface $participantSystem, EntityManagerInterface $entityManager): Response
    {
        
        $this->denyAccessUnlessGranted('CHAT_MANAGE', $chat);
        $submittedToken = $request->request->get('token');

        if($request->request->has('participants')) {
            if ($this->isCsrfTokenValid('private_chat_participant', $submittedToken)) {
                try {
                    $chat = $participantSystem->remove($chat, $request->request->get('participants'));
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('chat_private');
                }

                $this->addFlash('success','Participants were removed from chat.');
                return $this->redirectToRoute('chat_private');
            }
        }

        $this->addFlash('danger','Removing participants from the chat room fails.');
        return $this->redirectToRoute('chat_private');
    }

}
