<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Attachment;

use App\Services\Factory\Attachment\{AttachmentFactory, AttachmentFactoryInterface};
use PhpSpec\ObjectBehavior;
use App\Model\Attachment\AttachmentModel;
use App\Entity\{Attachment, User, Message, Chat};

class AttachmentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttachmentFactory::class);
    }

    function it_implements_attachment_factory_interface()
    {
        $this->shouldImplement(AttachmentFactoryInterface::class);
    }

    function it_should_be_able_to_create_attachment()
    {
        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $chat = new Chat();
        $chat
            ->setTitle('Chat title')
            ->setDescription('Chat description')
            ;

        $content = 'Example message content';

        $message = new Message();
        $message
            ->setContent($content)
            ->setOwner($user)
            ->setChat($chat)
            ;
        $attachmentModel = new AttachmentModel();
        $attachmentModel
            ->setMessage($message)
            ->setUser($user)
            ->setFilename('example.jpg')
            ->setType('Image')
            ;


        $attachment = $this->create($attachmentModel);
        $attachment->shouldBeAnInstanceOf(Attachment::class);
        $attachment->getUser()->shouldBeAnInstanceOf(User::class);
        $attachment->getUser()->getEmail()->shouldReturn('exampleuser@example.com');
        $attachment->getUser()->getLogin()->shouldReturn('exampleUser');
        $attachment->getMessage()->getContent()->shouldReturn('Example message content');
        $attachment->getFilename()->shouldReturn('example.jpg');
        $attachment->getType()->shouldReturn('Image');

    }
}
