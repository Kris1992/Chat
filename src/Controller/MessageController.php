<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\MessageCreator\MessageCreatorInterface;
use App\Services\PetitionMessageSystem\PetitionMessageSystemInterface;
use App\Repository\{ParticipantRepository, ChatMessageRepository};
use Symfony\Component\Mercure\{PublisherInterface, Update};
use Symfony\Component\Serializer\SerializerInterface;
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\Mailer\MailingSystemInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{Chat, Petition};

class MessageController extends AbstractController
{

    /**
     * @param   Chat                            $chat
     * @param   Request                         $request
     * @param   PublisherInterface              $publisher
     * @param   SerializerInterface             $serializer
     * @param   ParticipantRepository           $participantRepository
     * @param   JsonErrorResponseFactory        $jsonErrorFactory
     * @param   MessageCreatorInterface         $messageCreator
     * @param   EntityManagerInterface          $entityManager
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/chat/{id}/message", name="api_chat_message", methods={"POST"})
     * @IsGranted("ENTER_SITE")
     */
    public function addChatMessageAction(Chat $chat, Request $request, PublisherInterface $publisher, SerializerInterface $serializer, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory, MessageCreatorInterface $messageCreator, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('CHAT_WRITE', $chat);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        /** @var User $user */
        $user = $this->getUser();
        
        try {
            $message = $messageCreator->create($data['content'], $user, $chat, null);
            $entityManager->flush();
            $serializedMessage = $serializer->serialize(
                $message,
                'json', ['groups' => 'chat:message']
            );

            $topics = [
                sprintf('/chat/%d', $chat->getId())
            ];

            $othersParticipants = $participantRepository->findAllOthersParticipantsByChat($user, $chat);

            if ($othersParticipants) {
                foreach ($othersParticipants as $participant) {
                    $topics[] = sprintf('/%s', $participant->getUser()->getLogin());
                    $topics[] = sprintf('/account/%d/chats', $participant->getUser()->getId());
                }
            }

            $update = new Update(
                $topics,
                $serializedMessage,
                true
            );
        
            $publisher->__invoke($update);
            
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_MODEL_VALIDATION_ERROR, null, $e->getMessage());
        }
        
        return new JsonResponse($serializedMessage, Response::HTTP_CREATED);
    }

    /**
     * @param   Chat                        $chat
     * @param   PublisherInterface          $publisher
     * @param   SerializerInterface         $serializer
     * @param   ParticipantRepository       $participantRepository
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @return  Response
     * @Route("/api/chat/{id}/message/typing", name="api_chat_message_typing", methods={"GET"})
     * @IsGranted("ENTER_SITE")
     */
    public function typingMessage(Chat $chat, PublisherInterface $publisher, SerializerInterface $serializer, ParticipantRepository $participantRepository, JsonErrorResponseFactory $jsonErrorFactory): Response
    {
        //Check is user able to write messages
        $this->denyAccessUnlessGranted('CHAT_WRITE', $chat);
        
        /** @var User $user */
        $user = $this->getUser();
        
        //change to hateoas
        $serializedUser = $serializer->serialize(
            $user,
            'json', ['groups' => 'user:typing']
        );

        $topics = [
            sprintf('/chat/%d/message/typing', $chat->getId())
        ];
        
        $othersParticipants = $participantRepository->findAllOthersParticipantsByChat($user, $chat);
        
        if ($othersParticipants) {
            foreach ($othersParticipants as $participant) {
                $topics[] = sprintf('/%s', $participant->getUser()->getLogin());
            }
        }

        $update = new Update(
            $topics,
            $serializedUser,
            true
        );
    
        $publisher->__invoke($update);

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @param   Request                 $request
     * @param   Chat                    $chat
     * @param   ChatMessageRepository   $chatMessageRepository
     * @param   ParticipantRepository   $participantRepository
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/chat/{id}/get_messages", name="api_chat_get_messages", methods={"POST"})
     * @IsGranted("ENTER_SITE")
     */
    public function getChatMessagesAction(Request $request, Chat $chat, ChatMessageRepository $chatMessageRepository, ParticipantRepository $participantRepository): Response
    {   
        $this->denyAccessUnlessGranted('CHAT_VIEW', $chat);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        /** @var User $user */
        $user = $this->getUser();

        $participant = $participantRepository->findOneBy([
            'chat' => $chat,
            'user' => $user
        ]);


        $date = new \DateTime($data['messageDate']);
        $date->setTimezone(new \DateTimeZone('Europe/Berlin'));

        $times = $participant->getParticipateTimesBeforeDate($date);

        $allMessages = [];
        foreach ($times as $time) {

            $messages = $chatMessageRepository->findByChatAndPeriods(
                $chat,
                $time->getStartAt(),
                $time->getStopAt() ?? new \DateTime(),
                $date
            );

            $allMessages = array_merge($allMessages, $messages);
    
            if (count($allMessages) >= 10 || $times->last() === $time) {
                return $this->json(
                    $allMessages,
                    200,
                    [],
                    ['groups' => 'chat:message']
                );
            }
        }
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }

    /**
     * @param   Petition                            $petition
     * @param   Request                             $request
     * @param   JsonErrorResponseFactory            $jsonErrorFactory
     * @param   PetitionMessageSystemInterface      $petitionMessageSystem
     * @param   EntityManagerInterface              $entityManager
     * @param   MailingSystemInterface              $mailingSystem
     * @param   SerializerInterface                 $serializer
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/petition/{id}/message", name="api_petition_message", methods={"POST"})
     */
    public function addPetitionMessageAction(Petition $petition, Request $request, JsonErrorResponseFactory $jsonErrorFactory, PetitionMessageSystemInterface $petitionMessageSystem, EntityManagerInterface $entityManager, MailingSystemInterface $mailingSystem, SerializerInterface $serializer): Response
    {

        $this->denyAccessUnlessGranted('PETITION_WRITE', $petition);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }
        
        try {
            $message = $petitionMessageSystem->create(
                $data['content'],
                $this->getUser(),
                $petition
            );
            $entityManager->flush();
            if ($petition->getStatus() === 'Answered') {
                $mailingSystem->sendAnsweredPetitionMessage($petition);
            }

            $serializedMessage = $serializer->serialize(
                $message,
                'json', ['groups' => 'petition:message']
            );
            
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_MODEL_VALIDATION_ERROR, null, $e->getMessage());
        }
        
        return new JsonResponse($serializedMessage, Response::HTTP_CREATED);
    }

}
