<?php
declare(strict_types=1);

namespace App\Services\Factory\ChatModel;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

/**
 *  Take care about creating chat room model
 */
interface ChatModelFactoryInterface
{   

    /**
     * create Create chat room model
     * @param  Chat $chat   Chat room object
     * @return ChatModel    Return chat model object
     */
    public function create(Chat $chat): ChatModel;

}