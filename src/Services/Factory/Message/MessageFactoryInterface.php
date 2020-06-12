<?php
declare(strict_types=1);

namespace App\Services\Factory\Message;

use App\Model\Message\MessageModel;
use App\Entity\Message;

/**
 *  Take care about creating message object
 */
interface MessageFactoryInterface
{   

    /**
     * create message object from message model object
     * @param  MessageModel $messageModel Message model object
     * @return Message
     */
    public function create(MessageModel $messageModel): Message;

}
