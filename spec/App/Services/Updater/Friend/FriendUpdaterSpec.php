<?php declare(strict_types=1);

namespace spec\App\Services\Updater\Friend;

use App\Services\Updater\Friend\{FriendUpdater, FriendUpdaterInterface};
use PhpSpec\ObjectBehavior;
use App\Entity\{Friend, User};

class FriendUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FriendUpdater::class);
    }

    function it_implements_friend_updater_interface()
    {
        $this->shouldImplement(FriendUpdaterInterface::class);
    }

    function it_should_be_able_to_update_friend_by_accept()
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

        $friend = new Friend();
        $friend
            ->setInviter($inviter)
            ->setInvitee($invitee)
            ->setStatus('Pending')
            ;

        $friend = $this->update($friend, 'accept');
        $friend->shouldBeAnInstanceOf(Friend::class);
        $friend->getInviter()->getEmail()->shouldReturn('inviter@example.com');
        $friend->getInviter()->getLogin()->shouldReturn('inviter');
        $friend->getInvitee()->getEmail()->shouldReturn('invitee@example.com');
        $friend->getInvitee()->getLogin()->shouldReturn('invitee');
        $friend->getStatus()->shouldReturn('Accepted');

    }

    function it_should_be_able_to_update_friend_by_reject()
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

        $friend = new Friend();
        $friend
            ->setInviter($inviter)
            ->setInvitee($invitee)
            ->setStatus('Pending')
            ;

        $friend = $this->update($friend, 'reject');
        $friend->shouldBeAnInstanceOf(Friend::class);
        $friend->getInviter()->getEmail()->shouldReturn('inviter@example.com');
        $friend->getInviter()->getLogin()->shouldReturn('inviter');
        $friend->getInvitee()->getEmail()->shouldReturn('invitee@example.com');
        $friend->getInvitee()->getLogin()->shouldReturn('invitee');
        $friend->getStatus()->shouldReturn('Rejected');

    }

    function it_should_not_be_able_to_update_friend_by_fake_status()
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

        $friend = new Friend();
        $friend
            ->setInviter($inviter)
            ->setInvitee($invitee)
            ->setStatus('Pending')
            ;

        $friend = $this->update($friend, 'fakeone');
        $friend->shouldBeAnInstanceOf(Friend::class);
        $friend->getInviter()->getEmail()->shouldReturn('inviter@example.com');
        $friend->getInviter()->getLogin()->shouldReturn('inviter');
        $friend->getInvitee()->getEmail()->shouldReturn('invitee@example.com');
        $friend->getInvitee()->getLogin()->shouldReturn('invitee');
        $friend->getStatus()->shouldReturn('Pending');

    }

}
