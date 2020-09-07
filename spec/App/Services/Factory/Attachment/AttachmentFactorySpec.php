<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Attachment;

use App\Services\Factory\Attachment\{AttachmentFactory, AttachmentFactoryInterface};
use App\Services\Factory\Attachment\{MessageAttachmentFactory, PetitionAttachmentFactory};
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttachmentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttachmentFactory::class);
    }

    function it_is_able_to_create_message_attachment_factory() 
    {
        $this->beConstructedThrough('chooseFactory', ['Chat']);
        $this->shouldBeAnInstanceOf(MessageAttachmentFactory::class);
        $this->shouldImplement(AttachmentFactoryInterface::class);
    }

    function it_is_able_to_create_petition_attachment_factory() 
    {
        $this->beConstructedThrough('chooseFactory', ['Petition']);
        $this->shouldBeAnInstanceOf(PetitionAttachmentFactory::class);
        $this->shouldImplement(AttachmentFactoryInterface::class);
    }

    function it_should_throw_exception_when_choosen_factory_does_not_exist(){
        $this->shouldThrow('Exception')->during('chooseFactory', [Argument::type('string')]);
    }
}
