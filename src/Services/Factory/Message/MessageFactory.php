<?php declare(strict_types=1);

namespace App\Services\Factory\Message;

/**
 *  Manage ConcreteFactory
 */
class MessageFactory
{   

    const CHAT_MESSAGE_FACTORY="ChatMessage";
    const PETITION_MESSAGE_FACTORY="PetitionMessage";
 
    public static function chooseFactory($factoryName) {
        switch($factoryName) {
            case self::CHAT_MESSAGE_FACTORY:
                return new ChatMessageFactory();
            default:
                throw new \Exception("Unsupported type of message factory");
        }
    }
}

