<?php declare(strict_types=1);

namespace App\Services\Factory\FriendInvitation;

use App\Entity\{User, Friend};

class FriendInvitationFactory implements FriendInvitationFactoryInterface 
{
    
    public function create(User $inviter, User $invitee): Friend
    {
        $friendInvitation = new Friend();
        $friendInvitation
            ->setInviter($inviter)
            ->setInvitee($invitee)
            ->setStatus('Pending')
            ;

        return $friendInvitation;
    }
    
}
