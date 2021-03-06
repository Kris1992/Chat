<?php declare(strict_types=1);

namespace App\Services\Factory\Message;

use App\Model\Message\MessageModel;
use App\Entity\{ChatMessage, Message};

class ChatMessageFactory implements MessageFactoryInterface 
{
    
    public function create(MessageModel $messageModel): Message
    {
        
        $message = new ChatMessage();
        $message
            ->setContent($messageModel->getContent())
            ->setOwner($messageModel->getOwner())
            ->setChat($messageModel->getChat())
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
