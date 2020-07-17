<?php declare(strict_types=1);

namespace App\Services\Updater\Chat;

use App\Model\Chat\ChatModel;
use App\Entity\Chat;

/** 
 *  Interface for updating Chat entities
 */
interface ChatUpdaterInterface
{
    /**
     * update Update entity class with data from model class
     * @param   ChatModel $chatModel  Model data class which will used to update entity
     * @param   Chat      $chat       Chat object which will be updated
     * @return  Chat                  Updated chat
     */
    public function update(ChatModel $chatModel, Chat $chat): Chat;
}
