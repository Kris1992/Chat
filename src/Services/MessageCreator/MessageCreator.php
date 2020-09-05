<?php declare(strict_types=1);

namespace App\Services\MessageCreator;

use App\Services\Factory\MessageModel\MessageModelFactoryInterface;
use App\Services\Factory\Message\MessageFactory;
use App\Services\ModelValidator\{ModelValidatorInterface, ModelValidatorChooser};
use App\Entity\{User, Chat, Message, Petition};

class MessageCreator implements MessageCreatorInterface 
{

    /** @var MessageModelFactoryInterface */
    private $messageModelFactory;

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var ModelValidatorChooser */
    private $validatorChooser;

    /**
     * MessageCreator Constructor
     * 
     * @param MessageModelFactoryInterface $messageModelFactory
     * @param ModelValidatorInterface $modelValidator
     * @param ModelValidatorChooser $validatorChooser
     */
    public function __construct(MessageModelFactoryInterface $messageModelFactory, ModelValidatorInterface $modelValidator, ModelValidatorChooser $validatorChooser)  
    {
        $this->messageModelFactory = $messageModelFactory;
        $this->modelValidator = $modelValidator;
        $this->validatorChooser = $validatorChooser;
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

        switch ($messageType) {
            case 'ChatMessage':
                $chat->addMessage($message);
                $chat->setLastMessage($message);
                break;

            case 'PetitionMessage':
                $petition->addPetitionMessage($message);
                break;
        }  
                    
        return $message;
    }

}
