<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use App\Services\ModelValidator\ModelValidatorInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Message\Command\CheckUserActivityOnPublicChat;
use App\Services\Factory\ChatModel\ChatModelFactoryInterface;
use App\Services\Factory\Chat\ChatFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\{ChatRepository, ParticipantRepository, UserRepository};
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
        
        $chats = $chatRepository->findPrivateChatsByUser($user);

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

}
