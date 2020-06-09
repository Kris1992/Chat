<?php
declare(strict_types=1);

namespace spec\App\Services\Factory\UserModel;

use App\Services\Factory\UserModel\{UserModelFactoryInterface, UserModelFactory};
use PhpSpec\ObjectBehavior;
use App\Model\User\UserModel;
use App\Entity\User;

class UserModelFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UserModelFactory::class);
    }

    function it_impelements_user_model_factory_interface()
    {
        $this->shouldImplement(UserModelFactoryInterface::class);
    }

    function it_should_be_able_to_create_user_model()
    {
        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ->setGender('Male')
            ->setRoles(['ROLE_USER'])
            ;

        $userModel = $this->create($user);
        $userModel->shouldBeAnInstanceOf(UserModel::class);
        $userModel->getEmail()->shouldReturn('exampleuser@example.com');
        $userModel->getLogin()->shouldReturn('exampleUser');
        $userModel->getGender()->shouldReturn('Male');
        $userModel->isAdmin()->shouldReturn(false);
        $userModel->getRoles()->shouldBeArray();
        $userModel->getRoles()[0]->shouldReturn('ROLE_USER');

    }

}
