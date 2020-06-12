<?php
declare(strict_types=1);

namespace App\Services\Factory\MessageModel;

use App\Model\Message\MessageModel;
use App\Entity\{User,Chat};

class MessageModelFactory implements MessageModelFactoryInterface 
{
    
    public function createFromData(?string $content, ?User $owner, ?Chat $chat): MessageModel
    {
        
        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($owner)
            ->setChat($chat)
            ;

        return $messageModel;
    }

}
