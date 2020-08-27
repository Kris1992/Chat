<?php declare(strict_types=1);

namespace App\Services\MessageCreatorSystem;

use App\Entity\{User, Chat, Message};

/**
 *  Take care about all process of create and add message to chat
 */
interface MessageCreatorSystemInterface
{   
    /**
     * create Create, validate, save and add message to chat object
     * @param   ?string          $messageContent     String with content of message
     * @param   ?User            $user               User object whose is owner of message
     * @param   ?Chat            $chat               Chat object to save message in
     * @throws  \Exception                           Throws \Exception when create message fails
     * @return  Message                              Return message object
     */
    public function create(?string $messageContent, ?User $user, ?Chat $chat): Message;

}
