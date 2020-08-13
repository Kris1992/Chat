<?php declare(strict_types=1);

namespace App\Services\ParticipantSystem;

use App\Entity\Chat;

/**
 *  Take care about add/remove participants to/from existing chat
 */
interface ParticipantSystemInterface
{   

    /**
     * add Add participant to existing chat
     * @param   Chat                $chat           Chat object to update participants
     * @param   array|null          $usersIds       Array with users ids which will be added to chat
     * @throws  \Exception                          Throws exception when array of users ids is empty or proccess of adding fails
     * @return  Chat
     */
    public function add(Chat $chat, ?array $usersIds): Chat;

    /**
     * remove Remove participant from existing chat
     * @param   Chat                $chat                   Chat object to update participants
     * @param   array|null          $participantsIds        Array with participants ids which will be removed from chat
     * @throws  \Exception                                  Throws exception when array of participants is empty ...
     * @return  Chat
     */
    public function remove(Chat $chat, ?array $participantsIds): Chat;

}
