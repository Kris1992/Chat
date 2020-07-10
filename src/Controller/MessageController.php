<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Exception\Api\ApiBadRequestHttpException;
use App\Services\Factory\MessageModel\MessageModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Message\MessageFactoryInterface;
use Symfony\Component\Mercure\{PublisherInterface, Update};
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\{ParticipantRepository, MessageRepository};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Chat;

class MessageController extends AbstractController
{

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
                sprintf('/chat/%d', $chat->getId())
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

    /**
     * @Route("api/chat/{id}/get_messages", name="api_chat_get_messages", methods={"POST"})
     */
    public function getMessages(Request $request, Chat $chat, MessageRepository $messageRepository): Response
    {   
        $this->denyAccessUnlessGranted('CHAT_VIEW', $chat);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        $messages = $messageRepository->findBy(['chat' => $chat], ['createdAt' => 'DESC'], 5, $data['offset']);

        return $this->json(
                $messages,
                200,
                [],
                ['groups' => 'chat:message']
            );

    }

}
