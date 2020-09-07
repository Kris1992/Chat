<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Attachment;

use App\Services\Factory\Attachment\{MessageAttachmentFactory, AttachmentFactoryInterface};
use App\Entity\{MessageAttachment, User, ChatMessage, Chat};
use App\Model\Attachment\AttachmentModel;
use PhpSpec\ObjectBehavior;

class MessageAttachmentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MessageAttachmentFactory::class);
    }

    function it_implements_attachment_factory_interface()
    {
        $this->shouldImplement(AttachmentFactoryInterface::class);
    }

    function it_should_be_able_to_create_message_attachment()
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

        $message = new ChatMessage();
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
        $attachment->shouldBeAnInstanceOf(MessageAttachment::class);
        $attachment->getUser()->shouldBeAnInstanceOf(User::class);
        $attachment->getUser()->getEmail()->shouldReturn('exampleuser@example.com');
        $attachment->getUser()->getLogin()->shouldReturn('exampleUser');
        $attachment->getMessage()->getContent()->shouldReturn('Example message content');
        $attachment->getFilename()->shouldReturn('example.jpg');
        $attachment->getType()->shouldReturn('Image');

    }

}
