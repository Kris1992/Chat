<?php declare(strict_types=1);

namespace App\Services\Factory\MessageModel;

use App\Model\Message\MessageModel;
use App\Entity\{User,Chat, Petition};

/**
 *  Take care about creating message model object
 */
interface MessageModelFactoryInterface
{   

    /**
     * createFromData message model object from data
     * @param  string|null      $content    String with message content or null (content will be validate inside model)
     * @param  User|null        $owner      User object with owner of message or null
     * @param  Chat|null        $chat       Chat object (message dependent of) or null
     * @param  Petition|null    $petition   Petition object (message dependent of) or null
     * @return MessageModel          
     */
    public function createFromData(?string $content, ?User $owner, ?Chat $chat, ?Petition $petition): MessageModel;

}
