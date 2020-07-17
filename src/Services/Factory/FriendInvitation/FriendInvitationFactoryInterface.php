<?php declare(strict_types=1);

namespace App\Services\Factory\FriendInvitation;

use App\Entity\{User, Friend};

/**
 *  Manage creating friends invitations
 */
interface FriendInvitationFactoryInterface
{   

    /**
     * create Create friend invitation
     * @param   User    $inviter    User object whose is inviter
     * @param   User    $invitee    User object whose is invitee
     * @return  Friend
     */
    public function create(User $inviter, User $invitee): Friend;

}
