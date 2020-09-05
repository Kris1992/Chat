<?php declare(strict_types=1);

namespace App\Services\PetitionStatusChanger;

use App\Model\Petition\PetitionConstants;
use App\Entity\{User, Petition};
use App\Repository\PetitionMessageRepository;

class PetitionStatusChanger implements PetitionStatusChangerInterface 
{

    /** @var PetitionMessageRepository */
    private $petitionMessageRepository;

    /**
     * PetitionStatusChanger Constructor
     *
     * @param PetitionMessageRepository $petitionMessageRepository
     */
    public function __construct(PetitionMessageRepository $petitionMessageRepository)  
    {
        $this->petitionMessageRepository = $petitionMessageRepository;
    }

    public function change(User $user, Petition $petition, ?string $newStatus, bool $updateMessages): Petition
    {
        if (!in_array($newStatus, PetitionConstants::VALID_STATUSES, true) ) {
            throw new \Exception("Invalid petition status");
        }

        //just admins can change status to opened or answered and do not change to opened if user is admin and actual status is answered
        if ((in_array($newStatus, PetitionConstants::ADMIN_STATUSES, true) && !$user->isAdmin()) ||
            (in_array($newStatus, PetitionConstants::ADMIN_STATUSES, true) && $user->isAdmin()
            && $petition->getStatus() === 'Answered')
        ) {
            
        } else {
            $petition->setStatus($newStatus);
        }

        if ($updateMessages) {
            $messages = $this->petitionMessageRepository->findUnreadedByPetitionAndOtherUser($petition, $user);
            foreach ($messages as $message) {
                $message->setReaded();
            }
        } 
        
        return $petition;
    }

}
