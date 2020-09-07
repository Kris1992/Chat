<?php declare(strict_types=1);

namespace App\Services\PetitionMessageSystem;

use App\Services\PetitionStatusChanger\PetitionStatusChangerInterface;
use App\Services\MessageCreator\MessageCreatorInterface;
use App\Entity\{User, Message, Petition};

class PetitionMessageSystem implements PetitionMessageSystemInterface 
{

    /** @var MessageCreatorInterface */
    private $messageCreator;

    /** @var PetitionStatusChangerInterface */
    private $petitionStatusChanger;

    /**
     * PetitionMessageSystem Constructor
     * 
     * @param MessageCreatorInterface           $messageCreator
     * @param PetitionStatusChangerInterface    $petitionStatusChanger
     */
    public function __construct(MessageCreatorInterface $messageCreator, PetitionStatusChangerInterface $petitionStatusChanger)  
    {
        $this->messageCreator = $messageCreator;
        $this->petitionStatusChanger = $petitionStatusChanger;
    }

    public function create(?string $messageContent, ?User $user, ?Petition $petition): Message
    {
        $message = $this->messageCreator->create($messageContent, $user, null, $petition, 'PetitionMessage');
        
        if ($user->isAdmin()) {
            $this->petitionStatusChanger->change($user, $petition, 'Answered', false);
        } else {
            $this->petitionStatusChanger->change($user, $petition, 'Pending', false);
        }
                    
        return $message;
    }

}
