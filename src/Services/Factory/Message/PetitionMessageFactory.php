<?php declare(strict_types=1);

namespace App\Services\Factory\Message;

use App\Model\Message\MessageModel;
use App\Entity\{PetitionMessage, Message};

class PetitionMessageFactory implements MessageFactoryInterface 
{
    
    public function create(MessageModel $messageModel): Message
    {
        
        $message = new PetitionMessage();
        $message
            ->setContent($messageModel->getContent())
            ->setOwner($messageModel->getOwner())
            ->setPetition($messageModel->getPetition())
            ;

        $attachments = $messageModel->getAttachments();
        
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $message
                    ->addAttachment($attachment)
                    ;
            }
        }

        return $message;
    }

}
