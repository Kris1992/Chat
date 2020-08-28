<?php declare(strict_types=1);

namespace App\Services\MessageCreatorSystem;

use App\Services\Factory\MessageModel\MessageModelFactoryInterface;
use App\Services\Factory\Message\MessageFactory;
use App\Services\ModelValidator\{ModelValidatorInterface, ModelValidatorChooser};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{User, Chat, Message, Petition};

class MessageCreatorSystem implements MessageCreatorSystemInterface 
{

    /** @var MessageModelFactoryInterface */
    private $messageModelFactory;

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var ModelValidatorChooser */
    private $validatorChooser;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * MessageCreatorSystem Constructor
     * 
     * @param MessageModelFactoryInterface $messageModelFactory
     * @param ModelValidatorInterface $modelValidator
     * @param ModelValidatorChooser $validatorChooser
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MessageModelFactoryInterface $messageModelFactory, ModelValidatorInterface $modelValidator, ModelValidatorChooser $validatorChooser, EntityManagerInterface $entityManager)  
    {
        $this->messageModelFactory = $messageModelFactory;
        $this->modelValidator = $modelValidator;
        $this->validatorChooser = $validatorChooser;
        $this->entityManager = $entityManager;
    }

    public function create(?string $messageContent, ?User $user, ?Chat $chat, ?Petition $petition, string $messageType = 'ChatMessage'): Message
    {
        $messageModel = $this->messageModelFactory->createFromData($messageContent, $user, $chat, $petition);


        $isValid = $this->modelValidator->isValid(
            $messageModel, 
            $this->validatorChooser->chooseValidationGroup($messageType)
        );

        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }
        
        $messageFactory = MessageFactory::chooseFactory($messageType);
        $message = $messageFactory->create($messageModel);
        $chat->addMessage($message);
        $chat->setLastMessage($message);
        $this->entityManager->flush();   
                    
        return $message;
    }

}
