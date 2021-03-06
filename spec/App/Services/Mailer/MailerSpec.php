<?php declare(strict_types=1);

namespace spec\App\Services\Mailer;

use App\Services\Mailer\{MailingSystemInterface, Mailer};
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\{User, Petition};

class MailerSpec extends ObjectBehavior
{

    function let(MailerInterface $mailer)
    {
        $this->beConstructedWith($mailer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Mailer::class);
    }

    function it_implements_mailing_system_interface()
    {
        $this->shouldImplement(MailingSystemInterface::class);
    }

    function it_should_be_able_to_send_reset_password_message($mailer)
    {   
        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $message = $this->sendResetPasswordMessage($user);
        $mailer->send(Argument::any())->shouldBeCalledTimes(1);
        $message->shouldBeAnInstanceOf(TemplatedEmail::class);
        $message->getSubject()->shouldReturn('Reset password!');
        
        /** @var Address[] $addresses */
        $addresses = $message->getTo();
        $addresses->shouldHaveCount(1);
        $addresses[0]->shouldBeAnInstanceOf(Address::class);
        $addresses[0]->getName()->shouldBe('exampleUser');
        $addresses[0]->getAddress()->shouldBe('exampleuser@example.com');
    }
    
    function it_should_be_able_to_send_answered_petition_message($mailer)
    {   
        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $petition = new Petition();
        $petition
            ->setTitle('Petition title')
            ->setType('Ban')
            ->setDescription('Petition description')
            ->setPetitioner($user)
            ->setStatus('Answered')
            ;

        $message = $this->sendAnsweredPetitionMessage($petition);
        $mailer->send(Argument::any())->shouldBeCalledTimes(1);
        $message->shouldBeAnInstanceOf(TemplatedEmail::class);
        $message->getSubject()->shouldReturn('Your petition has new answer!');
        
        /** @var Address[] $addresses */
        $addresses = $message->getTo();
        $addresses->shouldHaveCount(1);
        $addresses[0]->shouldBeAnInstanceOf(Address::class);
        $addresses[0]->getName()->shouldBe('exampleUser');
        $addresses[0]->getAddress()->shouldBe('exampleuser@example.com');
        
    }
    
}
