<?php declare(strict_types=1);

namespace App\Services\Factory\Participant;

use App\Entity\{Chat, User, Participant, ParticipateTime};

class ParticipantFactory implements ParticipantFactoryInterface 
{
    
    public function create(User $user, ?Chat $chat): Participant
    {

        $participant = new Participant();
        $participant
            ->setUser($user)
            ->setChat($chat)
            ->addParticipateTime(new ParticipateTime())
            ->setIsRemoved(false)
            ->updateLastSeenAt()
            ;

        return $participant;
    }

}
