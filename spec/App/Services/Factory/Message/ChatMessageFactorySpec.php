<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Message;

use App\Services\Factory\Message\{ChatMessageFactory, MessageFactoryInterface};
use App\Entity\{Message, User, Chat, MessageAttachment, ChatMessage};
use App\Model\Message\MessageModel;
use PhpSpec\ObjectBehavior;

class ChatMessageFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChatMessageFactory::class);
    }

    function it_implements_message_factory_interface()
    {
        $this->shouldImplement(MessageFactoryInterface::class);
    }

    function it_should_be_able_to_create_chat_message_without_attachments()
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

        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($user)
            ->setChat($chat)
            ;

        $message = $this->create($messageModel);
        $message->shouldBeAnInstanceOf(ChatMessage::class);
        $message->getChat()->getTitle()->shouldReturn('Chat title');
        $message->getChat()->getDescription()->shouldReturn('Chat description');
        $message->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $message->getOwner()->getLogin()->shouldReturn('exampleUser');
        $message->getContent()->shouldReturn('Example message content');
    }

    function it_should_be_able_to_create_chat_message_with_attachments()
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

        $attachment = new MessageAttachment();
        $attachment
            ->setUser($user)
            ->setFilename('example.jpeg')
            ->setType('Image')
            ;

        $content = 'Example message content';

        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($user)
            ->setChat($chat)
            ->addAttachment($attachment)
            ;

        $message = $this->create($messageModel);
        $message->shouldBeAnInstanceOf(ChatMessage::class);
        $message->getChat()->getTitle()->shouldReturn('Chat title');
        $message->getChat()->getDescription()->shouldReturn('Chat description');
        $message->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $message->getOwner()->getLogin()->shouldReturn('exampleUser');
        $message->getContent()->shouldReturn('Example message content');

        $attachments = $message->getAttachments();
        $attachments[0]->getUser()->shouldBeAnInstanceOf(User::class);
        $attachments[0]->getFilename()->shouldReturn('example.jpeg');
        $attachments[0]->getType()->shouldReturn('Image');
    }

}
