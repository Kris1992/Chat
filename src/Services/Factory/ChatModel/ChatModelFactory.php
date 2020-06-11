<?php
declare(strict_types=1);

namespace App\Services\Factory\ChatModel;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

class ChatModelFactory implements ChatModelFactoryInterface 
{
    
    public function create(Chat $chat): ChatModel
    {

        $chatModel = new ChatModel();
        $chatModel
            ->setTitle($chat->getTitle())
            ->setDescription($chat->getDescription())
            ->setIsPublic($chat->getIsPublic())
            ;
            //add users if is not public
        return $chatModel;
    }

}
