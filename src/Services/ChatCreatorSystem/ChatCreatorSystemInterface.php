<?php declare(strict_types=1);

namespace App\Services\ChatCreatorSystem;

use App\Entity\{User, Chat};

/**
 *  Take care about all process of create chat
 */
interface ChatCreatorSystemInterface
{   
    /**
     * create Create, validate and save private chat object
     * @param   User            $owner              User object whose is owner of message
     * @param   array           $usersIds           Array with users ids (without owner)
     * @throws  \Exception                          Throws \Exception when create chat fails
     * @return  Chat                                Return chat object
     */
    public function create(User $owner, array $usersIds): Chat;

}
