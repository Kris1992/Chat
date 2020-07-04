<?php
declare(strict_types=1);

namespace App\Services\Factory\ChatModel;

use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

/**
 *  Take care about creating chat room model
 */
interface ChatModelFactoryInterface
{   

    /**
     * create Create chat room model
     * @param   Chat        $chat   Chat room object
     * @return  ChatModel           Return chat model object
     */
    public function create(Chat $chat): ChatModel;

    /**
     * createFromData Create chat room model from data
     * @param   User            $owner          User object whose is owner of chat
     * @param   bool            $isPublic       Boolean (if chat is public true otherwise false)
     * @param   array|null      $users          Array with users whose will be participants of chat room or null [optional]
     * @param   string|null     $title          String with title of chat or null [optional] 
     * @param   string|null     $description    String with description of chat or null [optional]
     * @return  ChatModel                       Return chat model object
     */
    public function createFromData(User $owner, bool $isPublic, ?array $users, ?string $title, ?string $description): ChatModel;

}