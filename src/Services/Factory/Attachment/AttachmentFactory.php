<?php declare(strict_types=1);

namespace App\Services\Factory\Attachment;

/**
 *  Manage ConcreteFactory
 */
class AttachmentFactory
{   

    const CHAT_ATTACHMENT_FACTORY="Chat";
    const PETITION_ATTACHMENT_FACTORY="Petition";
 
    public static function chooseFactory($factoryName) {
        switch($factoryName) {
            case self::CHAT_ATTACHMENT_FACTORY:
                return new MessageAttachmentFactory();
            case self::PETITION_ATTACHMENT_FACTORY:
                return new PetitionAttachmentFactory();
            default:
                throw new \Exception("Unsupported type of attachment factory");
        }
    }
}
