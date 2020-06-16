<?php
declare(strict_types=1);

namespace App\Services\Factory\Participant;

use App\Entity\{Chat, User, Participant};

/**
 *  Take care about creating participant of chat room 
 */
interface ParticipantFactoryInterface
{   

    /**
     * create Create participants of chat room
     * @param  User      $user      User object whose is participate chat
     * @param  Chat      $chat      Chat object
     * @return Participant          Return participant object
     */
    public function create(User $user, Chat $chat): Participant;

}