<?php
declare(strict_types=1);

namespace App\Services\Updater\Chat;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

class ChatUpdater implements ChatUpdaterInterface 
{   

    public function update(ChatModel $chatModel, Chat $chat): Chat
    {

        $chat
            ->setTitle($chatModel->getTitle())
            ->setDescription($chatModel->getDescription())
            ;
        
        return $chat;
    }

}
