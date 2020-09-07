<?php declare(strict_types=1);

namespace App\Services\PetitionMessageSystem;

use App\Entity\{User, Message, Petition};

/**
 *  Take care about all process of create and add message to petition and set proper status to petition
 */
interface PetitionMessageSystemInterface
{   
    /**
     * create Create, validate and add message to petition object with setting proper petition status (and send email message if petition change status to answered)
     * @param   ?string          $messageContent     String with content of message or null
     * @param   ?User            $user               User object whose is owner of message or null
     * @param   ?Petition        $petition           Petition object to save message in or null
     * @throws  \Exception                           Throws an \Exception when create message fails
     * @return  Message                              Return message object
     */
    public function create(?string $messageContent, ?User $user, ?Petition $petition): Message;

}
