<?php declare(strict_types=1);

namespace App\Services\MessageCreatorSystem;

use App\Services\Factory\MessageModel\MessageModelFactoryInterface;
use App\Services\Factory\Message\MessageFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{User, Chat, Message};

class MessageCreatorSystem implements MessageCreatorSystemInterface 
{

    /** @var MessageModelFactoryInterface */
    private $messageModelFactory;

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var MessageFactoryInterface */
    private $messageFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * MessageCreatorSystem Constructor
     * 
     * @param MessageModelFactoryInterface $messageModelFactory
     * @param ModelValidatorInterface $modelValidator
     * @param MessageFactoryInterface $messageFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MessageModelFactoryInterface $messageModelFactory, ModelValidatorInterface $modelValidator, MessageFactoryInterface $messageFactory, EntityManagerInterface $entityManager)  
    {
        $this->messageModelFactory = $messageModelFactory;
        $this->modelValidator = $modelValidator;
        $this->messageFactory = $messageFactory;
        $this->entityManager = $entityManager;
    }

    public function create(?string $messageContent, ?User $user, ?Chat $chat): Message
    {
        $messageModel = $this->messageModelFactory->createFromData($messageContent, $user, $chat);

        $isValid = $this->modelValidator->isValid($messageModel);

        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }
        
        $message = $this->messageFactory->create($messageModel);
        $chat->addMessage($message);
        $chat->setLastMessage($message);
        $this->entityManager->flush();   
                    
        return $message;
    }

}
