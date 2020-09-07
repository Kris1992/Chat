<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Message;

use App\Services\Factory\Message\{MessageFactory, MessageFactoryInterface};
use App\Services\Factory\Message\{ChatMessageFactory, PetitionMessageFactory};
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MessageFactory::class);
    }

    function it_is_able_to_create_chat_message_factory() 
    {
        $this->beConstructedThrough('chooseFactory', ['ChatMessage']);
        $this->shouldBeAnInstanceOf(ChatMessageFactory::class);
        $this->shouldImplement(MessageFactoryInterface::class);
    }

    function it_is_able_to_create_petition_message_factory() 
    {
        $this->beConstructedThrough('chooseFactory', ['PetitionMessage']);
        $this->shouldBeAnInstanceOf(PetitionMessageFactory::class);
        $this->shouldImplement(MessageFactoryInterface::class);
    }

    function it_should_throw_exception_when_choosen_factory_does_not_exist(){
        $this->shouldThrow('Exception')->during('chooseFactory', [Argument::type('string')]);
    }

}
