<?php declare(strict_types=1);

namespace App\Services\ParticipantSystem;

use App\Services\Factory\Participant\ParticipantFactoryInterface;
use App\Repository\{UserRepository, ParticipantRepository};
use App\Entity\{Chat, ParticipateTime};

class ParticipantSystem implements ParticipantSystemInterface 
{

    /** @var UserRepository */
    private $userRepository;

    /** @var ParticipantRepository */
    private $participantRepository;

    /** @var ParticipantFactoryInterface */
    private $participantFactory;

    /**
     * ParticipantSystem Constructor
     * 
     * @param UserRepository                    $userRepository
     * @param ParticipantRepository             $participantRepository
     * @param ParticipantFactoryInterface       $participantFactory
     */
    public function __construct(UserRepository $userRepository, ParticipantRepository $participantRepository, ParticipantFactoryInterface $participantFactory)  
    {
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
        $this->participantFactory = $participantFactory;
    }

    public function add(Chat $chat, ?array $usersIds): Chat
    {   

        $users = $this->userRepository->findAllByIds($usersIds);

        if (!$users) {
            throw new \Exception('Users to add not found.');
        }

        $existingParticipants = $this->participantRepository->findAllByUsersAndChat($users, $chat);
        
        $existingUsers[] = null;
        foreach ($existingParticipants as $existingParticipant) {
            $existingUsers[] = $existingParticipant->getUser();
            if ($existingParticipant->getIsRemoved()) {
                $existingParticipant->setIsRemoved(false);
                $existingParticipant->addParticipateTime(new ParticipateTime());
            }
        }

        //This can be reached by array_udiff too
        if (count($existingUsers) !== count($users)) {
            foreach ($users as $user) {
                if (!in_array($user, $existingUsers, true)) {
                    //Create completely new participant
                    $chat->addParticipant($this->participantFactory->create($user, $chat));
                }
            }
        }

        return $chat;
    }

    public function remove(Chat $chat, ?array $participantsIds): Chat
    {

        $existingParticipants = $this->participantRepository->findAllByIdsAndChat($participantsIds, $chat);
        
        if (!$existingParticipants) {
            throw new \Exception('Participants to remove not found.');
        }

        foreach ($existingParticipants as $existingParticipant) {
            $lastParticipate = $existingParticipant->getParticipateTimes()->last();
            if ($lastParticipate) {
                $existingParticipant->setIsRemoved(true);
                $lastParticipate->setStopAt(new \DateTime());
            }
        }

        return $chat;
    }

}
