<?php
declare(strict_types=1);

namespace App\Services\Factory\Chat;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

/**
 *  Take care about creating chat rooms
 */
interface ChatFactoryInterface
{   

    /**
     * create Create chat rooms
     * @param  ChatModel $chatModel Model with chat room data
     * @return Chat                 Return chat object
     */
    public function create(ChatModel $chatModel): Chat;

}