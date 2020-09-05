<?php declare(strict_types=1);

namespace App\Services\MessageCreator;

use App\Entity\{User, Chat, Message, Petition};

/**
 *  Take care about all process of create and add message to chat
 */
interface MessageCreatorInterface
{   
    /**
     * create Create, validate and add message to chat or petition object
     * @param   ?string          $messageContent     String with content of message or null
     * @param   ?User            $user               User object whose is owner of message or null
     * @param   ?Chat            $chat               Chat object to save message in or null
     * @param   ?Petition        $petition           Petition object to save message in or null
     * @param   string           $messageType        String with message type to create [optional]
     * @throws  \Exception                           Throws \Exception when create message fails
     * @return  Message                              Return message object
     */
    public function create(?string $messageContent, ?User $user, ?Chat $chat, ?Petition $petition, string $messageType = 'ChatMessage'): Message;

}
