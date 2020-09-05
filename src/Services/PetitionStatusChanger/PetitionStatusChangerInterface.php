<?php declare(strict_types=1);

namespace App\Services\PetitionStatusChanger;

use App\Entity\{User, Petition};

/**
 *  Take care about change status of petition and messages (set readed date to messages and change petition status )
 */
interface PetitionStatusChangerInterface
{   
    /**
     * change Change status of petition with update messages readedAt date or not
     * @param   User                $user               User object whose change status
     * @param   Petition            $petition           Petition object to update
     * @param   string|null         $newStatus          String with new status
     * @param   bool                $updateMessages     Update messages readedAt or not
     * @throws  \Exception                              Throws \Exception update petition fails
     * @return  Petition                                Return petition object
     */
    public function change(User $user, Petition $petition, ?string $newStatus, bool $updateMessages): Petition;

}

