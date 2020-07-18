<?php declare(strict_types=1);

namespace spec\App\Services\Factory\FriendInvitation;

use App\Services\Factory\FriendInvitation\{FriendInvitationFactory, FriendInvitationFactoryInterface};
use PhpSpec\ObjectBehavior;
use App\Entity\{User, Friend};

class FriendInvitationFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FriendInvitationFactory::class);
    }

    function it_implements_friend_invitation_factory_interface()
    {
        $this->shouldImplement(FriendInvitationFactoryInterface::class);
    }

    function it_should_be_able_to_create_friend_invitation()
    {
        $inviter = new User();
        $inviter
            ->setEmail('inviter@example.com')
            ->setLogin('inviter')
            ;
        
        $invitee = new User();
        $invitee
            ->setEmail('invitee@example.com')
            ->setLogin('invitee')
            ;

        $friend = $this->create($inviter, $invitee);
        $friend->shouldBeAnInstanceOf(Friend::class);
        $friend->getInviter()->getEmail()->shouldReturn('inviter@example.com');
        $friend->getInviter()->getLogin()->shouldReturn('inviter');
        $friend->getInvitee()->getEmail()->shouldReturn('invitee@example.com');
        $friend->getInvitee()->getLogin()->shouldReturn('invitee');
        $friend->getStatus()->shouldReturn('Pending');
    }

}
