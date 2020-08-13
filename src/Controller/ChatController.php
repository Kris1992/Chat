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
use App\Message\Command\{CheckUserActivityOnPublicChat, RemoveScreenFile};
use App\Services\ParticipantSystem\ParticipantSystemInterface;
use App\Services\Factory\ChatModel\ChatModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Chat\ChatFactoryInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\ChatPrinter\ChatPrinter;
use Symfony\Component\WebLink\Link;
use App\Entity\Chat;

/**
* @IsGranted("ROLE_USER")
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

        /** @var User $user */
        $user = $this->getUser();
        
        $chats = $chatRepository->findPrivateChatsByUser($user, 0);

        return $this->render('chat/private_list.html.twig', [
            'chats' => $chats
        ]);
    }

    /**
     * @param   Request                     $request
     * @param   EntityManagerInterface      $entityManager
     * @param   UserRepository              $userRepository
     * @param   ChatModelFactoryInterface   $chatModelFactory
     * @param   ModelValidatorInterface     $modelValidator
     * @param   ChatFactoryInterface        $chatFactory
     * @return  Response
     * @Route("/chat/private/create", name="chat_private_create", methods={"POST"})
     */
    public function createPrivateRoom(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, ChatModelFactoryInterface $chatModelFactory, ModelValidatorInterface $modelValidator, ChatFactoryInterface $chatFactory): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $submittedToken = $request->request->get('token');

        if($request->request->has('friends')) {
            if ($this->isCsrfTokenValid('private_chat', $submittedToken)) {
                $usersIds = $request->request->get('friends');
                $users = $userRepository->findAllByIds($usersIds);

                $chatModel = $chatModelFactory->createFromData($user, false, $users, null, null);
                $isValid = $modelValidator->isValid($chatModel, "chat:private");

                if ($isValid) {
                    $chat = $chatFactory->create($chatModel, $user);
                    
                    $entityManager->persist($chat);
                    $entityManager->flush();   
                    
                    $this->addFlash('success','Chat was created.');
                    return $this->redirectToRoute('chat_private');
                } else {
                    $this->addFlash('danger', $modelValidator->getErrorMessage());
                    return $this->redirectToRoute('chat_private');
                }

            }
        }

        $this->addFlash('danger','Cannot create this chat room.');
        return $this->redirectToRoute('chat_private');
    }

    /**
     * @param   Request             $request
     * @param   ChatRepository      $chatRepository
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/chat/private", name="api_get_chats", methods={"POST"})
     */
    public function getChats(Request $request, ChatRepository $chatRepository): Response
    {   

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        /** @var User $user */
        $user = $this->getUser();
        $chats = $chatRepository->findPrivateChatsByUser($user, $data['offset']);

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
    public function updateParticipant(Chat $chat, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory): Response
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
     * @param   Chat                        $chat
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @return  Response
     * @Route("/api/chat/{id}/other_participants", name="api_chat_get_other__participants", methods={"GET"})
     */
    public function getParticipants(Chat $chat, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $participants = $chat->getOtherParticipants($user);

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
    public function getHubUrl(Request $request): Response
    {
        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        return new Response(null, Response::HTTP_OK);
    }

    /**
     * @param   Chat                            $chat
     * @param   Request                         $request
     * @param   ChatPrinter                     $chatPrinter
     * @param   JsonErrorResponseFactory        $jsonErrorFactory
     * @param   MessageBusInterface             $messageBus
     * @return  Response
     * @Route("/api/chat/{id}/file", name="api_get_chat_data_file", methods={"POST"})
     */
    public function getChatDataAsFile(Chat $chat, Request $request, ChatPrinter $chatPrinter, JsonErrorResponseFactory $jsonErrorFactory, MessageBusInterface $messageBus): Response
    {
        $this->denyAccessUnlessGranted('CHAT_VIEW', $chat);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        $startAt = new \DateTime($data['startAt']);
        $stopAt = new \DateTime($data['stopAt']);

        $messages = $chat->getMessagesBetween($startAt, $stopAt);

        if ($messages->isEmpty()) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR, null, 'There is no messages to print in given interval.');        
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $concretePrinter = $chatPrinter->choosePrinter($data['fileFormat']);
            $link = $concretePrinter->printToFile($messages, $user, $startAt, $stopAt);
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR, null, $e->getMessage());
        }

        $message = new RemoveScreenFile(basename($link));
        $envelope = new Envelope($message, [
            new DelayStamp(900000)//15 minutes delay 
        ]);
        $messageBus->dispatch($envelope);

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
                            //check user can manage chat !important

        /** @var User $user */
        $user = $this->getUser();

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
        //check user can manage chat !important

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
