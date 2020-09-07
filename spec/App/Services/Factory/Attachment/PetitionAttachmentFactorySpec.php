<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Attachment;

use App\Services\Factory\Attachment\{PetitionAttachmentFactory, AttachmentFactoryInterface};
use App\Entity\{PetitionAttachment, User, Petition};
use App\Model\Attachment\AttachmentModel;
use PhpSpec\ObjectBehavior;

class PetitionAttachmentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PetitionAttachmentFactory::class);
    }

    function it_implements_attachment_factory_interface()
    {
        $this->shouldImplement(AttachmentFactoryInterface::class);
    }

    function it_should_be_able_to_create_petition_attachment()
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
            ->setStatus('Opened')
            ;

        $attachmentModel = new AttachmentModel();
        $attachmentModel
            ->setPetition($petition)
            ->setUser($user)
            ->setFilename('example.jpg')
            ->setType('Image')
            ;

        $attachment = $this->create($attachmentModel);
        $attachment->shouldBeAnInstanceOf(PetitionAttachment::class);
        $attachment->getUser()->shouldBeAnInstanceOf(User::class);
        $attachment->getUser()->getEmail()->shouldReturn('exampleuser@example.com');
        $attachment->getUser()->getLogin()->shouldReturn('exampleUser');
        $attachment->getPetition()->getTitle()->shouldReturn('Petition title');
        $attachment->getPetition()->getType()->shouldReturn('Ban');
        $attachment->getPetition()->getDescription()->shouldReturn('Petition description');
        $attachment->getPetition()->getPetitioner()->shouldBeAnInstanceOf(User::class);
        $attachment->getPetition()->getStatus()->shouldReturn('Opened');
        $attachment->getFilename()->shouldReturn('example.jpg');
        $attachment->getType()->shouldReturn('Image');

    }
    
}
