<?php
declare(strict_types=1);

namespace App\Services\Factory\Chat;

use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

/**
 *  Take care about creating chat rooms
 */
interface ChatFactoryInterface
{   

    /**
     * create Create chat rooms
     * @param  ChatModel $chatModel Model with chat room data
     * @param  User      $owner     User object whose is the owner of chat room
     * @return Chat                 Return chat object
     */
    public function create(ChatModel $chatModel, User $owner): Chat;

}