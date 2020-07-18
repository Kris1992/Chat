<?php declare(strict_types=1);

namespace spec\App\Services\Factory\User;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Services\Factory\User\{UserFactoryInterface, UserFactory};
use App\Model\User\UserModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\User;

class UserFactorySpec extends ObjectBehavior
{
    function let(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->beConstructedWith($passwordEncoder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserFactory::class);
    }

    function it_implements_user_factory_interface()
    {
        $this->shouldImplement(UserFactoryInterface::class);
    }

    function it_should_be_able_to_create_user($passwordEncoder)
    {
        $user = new User();
        $userModel = new UserModel();
        $userModel
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ->setGender('Male')
            ->setPlainPassword('example01')
            ->setAgreeTerms(true)
            ;
        $passwordEncoder->encodePassword(Argument::type(User::class), Argument::type('string'))->willReturn('example01');

        $user = $this->create($userModel, null, null);
        $user->shouldBeAnInstanceOf(User::class);
        $user->getEmail()->shouldReturn('exampleuser@example.com');
        $user->getLogin()->shouldReturn('exampleUser');
        $user->getGender()->shouldReturn('Male');
        $user->isAdmin()->shouldReturn(false);

    }

    function it_should_be_able_to_create_admin($passwordEncoder)
    {
        $user = new User();
        $userModel = new UserModel();
        $userModel
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ->setGender('Male')
            ->setPlainPassword('example01')
            ->setAgreeTerms(true)
            ;
        $passwordEncoder->encodePassword(Argument::type(User::class), Argument::type('string'))->willReturn('example01');

        $user = $this->create($userModel, ['ROLE_USER', 'ROLE_ADMIN'], null);
        $user->shouldBeAnInstanceOf(User::class);
        $user->isAdmin()->shouldReturn(true);

    }
    
    function it_should_not_allow_to_create_user_whose_not_accept_terms($passwordEncoder)
    {
        $user = new User();
        $userModel = new UserModel();
        $userModel
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ->setGender('Male')
            ->setPlainPassword('example01')
            ;
        $passwordEncoder->encodePassword(Argument::type(User::class), Argument::type('string'))->willReturn('example01');

        $this->shouldThrow(\Exception::class)->during('create', [$userModel, null, null]);
    }

}
