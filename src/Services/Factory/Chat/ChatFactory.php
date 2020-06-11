<?php
declare(strict_types=1);

namespace App\Services\Factory\Chat;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

class ChatFactory implements ChatFactoryInterface 
{
    
    public function create(ChatModel $chatModel): Chat
    {

        /* From admin area only public chat rooms */
        if ($chatModel->getIsPublic() === null) {
            $chatModel->setIsPublic(true);   
        }

        $chat = new Chat();
        $chat
            ->setTitle($chatModel->getTitle())
            ->setDescription($chatModel->getDescription())
            ->setIsPublic($chatModel->getIsPublic())
            ;
            //add users if is not public
        return $chat;
    }

}
