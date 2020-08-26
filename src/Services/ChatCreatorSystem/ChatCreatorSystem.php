<?php declare(strict_types=1);

namespace App\Services\ChatCreatorSystem;

use App\Services\Factory\ChatModel\ChatModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Chat\ChatFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\{User, Chat};

class ChatCreatorSystem implements ChatCreatorSystemInterface 
{

    /** @var UserRepository */
    private $userRepository;

    /** @var ChatModelFactoryInterface */
    private $chatModelFactory;

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var ChatFactoryInterface */
    private $chatFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * ChatCreatorSystem Constructor
     * 
     * @param UserRepository $userRepository
     * @param ChatModelFactoryInterface $chatModelFactory
     * @param ModelValidatorInterface $modelValidator
     * @param ChatFactoryInterface $chatFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(UserRepository $userRepository, ChatModelFactoryInterface $chatModelFactory, ModelValidatorInterface $modelValidator, ChatFactoryInterface $chatFactory, EntityManagerInterface $entityManager)  
    {
        $this->userRepository = $userRepository;
        $this->chatModelFactory = $chatModelFactory;
        $this->modelValidator = $modelValidator;
        $this->chatFactory = $chatFactory;
        $this->entityManager = $entityManager;
    }

    public function create(User $owner, array $usersIds): Chat
    {

        $users = $this->userRepository->findAllByIds($usersIds);
        $chatModel = $this->chatModelFactory->createFromData($owner, false, $users, null, null);
        
        $isValid = $this->modelValidator->isValid($chatModel, "chat:private");

        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }
        
        $chat = $this->chatFactory->create($chatModel, $owner, null);
        $this->entityManager->persist($chat);
        $this->entityManager->flush();   
                    
        return $chat;
    }

}
