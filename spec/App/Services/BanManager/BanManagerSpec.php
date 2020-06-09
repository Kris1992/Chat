<?php
declare(strict_types=1);

namespace spec\App\Services\BanManager;

use App\Services\BanManager\{BanManagerInterface, BanManager};
use PhpSpec\ObjectBehavior;
use App\Entity\User;

class BanManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BanManager::class);
    }

    function it_impelements_ban_manager_interface()
    {
        $this->shouldImplement(BanManagerInterface::class);
    }

    function it_should_throw_exception_when_try_ban_admin_user()
    {
        $user = new User();
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);

        $this->shouldThrow('Exception')->during('ban', [$user, 1]);
    }

    function it_should_throw_exception_when_try_ban_already_banned_user()
    {
        $user = new User();
        $user->setBanTo(new \DateTime('now +1 day'));

        $this->shouldThrow('Exception')->during('ban', [$user, 1]);
    }

    function it_should_throw_exception_when_ban_option_is_not_defined()
    {
        $user = new User();

        $this->shouldThrow('Exception')->during('ban', [$user, 8]);
    }

    function it_is_able_to_ban_user()
    {
        $user = new User();

        $user = $this->ban($user, 0);
        $user->shouldBeAnInstanceOf(User::class);
        $user->isBanned()->shouldReturn(true);
    }

    function it_should_throw_exception_when_try_un_ban_not_banned_user()
    {
        $user = new User();

        $this->shouldThrow('Exception')->during('unBan', [$user]);
    }

    function it_is_able_to_un_ban_banned_user()
    {
        $user = new User();
        $user->setBanTo(new \DateTime('now +1 day'));

        $user = $this->unBan($user);
        $user->shouldBeAnInstanceOf(User::class);
        $user->isBanned()->shouldReturn(false);
    }

}
