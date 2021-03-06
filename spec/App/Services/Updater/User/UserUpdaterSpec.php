<?php declare(strict_types=1);

namespace spec\App\Services\Updater\User;

use App\Services\Updater\User\{UserUpdater, UserUpdaterInterface};
use App\Services\ImagesManager\ImagesManagerInterface;
use PhpSpec\ObjectBehavior;
use App\Model\User\UserModel;
use App\Entity\User;

class UserUpdaterSpec extends ObjectBehavior
{

    function let(ImagesManagerInterface $userImagesManager)
    {
        $this->beConstructedWith($userImagesManager);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType(UserUpdater::class);
    }

    function it_implements_user_updater_interface()
    {
        $this->shouldImplement(UserUpdaterInterface::class);
    }

    function it_should_be_able_to_update_user()
    {

        $user = new User();
        $user
            ->setEmail('prevuser@example.com')
            ->setLogin('PrevLogin')
            ->setGender('Female')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ;

        $userModel = new UserModel();
        $userModel
            ->setEmail('newuser@example.com')
            ->setGender('Male')
            ->setRoles(['ROLE_USER'])
            ;

        $user = $this->update($userModel, $user, null);
        $user->shouldBeAnInstanceOf(User::class);
        $user->getEmail()->shouldReturn('newuser@example.com');
        $user->getLogin()->shouldReturn('PrevLogin');
        $user->getGender()->shouldReturn('Male');
        $user->isAdmin()->shouldReturn(false);

    }

}
