<?php
declare(strict_types=1);

namespace App\Services\Factory\Message;

use App\Model\Message\MessageModel;
use App\Entity\Message;

class MessageFactory implements MessageFactoryInterface 
{
    
    public function create(MessageModel $messageModel): Message
    {
        
        $message = new Message();
        $message
            ->setContent($messageModel->getContent())
            ->setOwner($messageModel->getOwner())
            ->setChat($messageModel->getChat())
            ;

        return $message;
    }

}
