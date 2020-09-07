<?php declare(strict_types=1);

namespace spec\App\Services\Factory\MessageModel;

use App\Services\Factory\MessageModel\{MessageModelFactory, MessageModelFactoryInterface};
use App\Services\AttachmentHelper\AttachmentHelperInterface;
use App\Entity\{User, Chat, Petition};
use App\Model\Message\MessageModel;
use PhpSpec\ObjectBehavior;

class MessageModelFactorySpec extends ObjectBehavior
{

    function let(AttachmentHelperInterface $attachmentHelper)
    {
        $this->beConstructedWith($attachmentHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MessageModelFactory::class);
    }

    function it_should_be_able_to_create_chat_message_model_from_data()
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

        $messageModel = $this->createFromData($content, $user, $chat, null);
        $messageModel->shouldBeAnInstanceOf(MessageModel::class);
        $messageModel->getChat()->getTitle()->shouldReturn('Chat title');
        $messageModel->getChat()->getDescription()->shouldReturn('Chat description');
        $messageModel->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $messageModel->getOwner()->getLogin()->shouldReturn('exampleUser');
        $messageModel->getContent()->shouldReturn('Example message content');
    }

    function it_should_be_able_to_create_petition_message_model_from_data()
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
            ->setStatus('Opened')
            ;

        $content = 'Example message content';

        $messageModel = $this->createFromData($content, $user, null, $petition);
        $messageModel->shouldBeAnInstanceOf(MessageModel::class);
        $messageModel->getPetition()->getTitle()->shouldReturn('Petition title');
        $messageModel->getPetition()->getType()->shouldReturn('Ban');
        $messageModel->getPetition()->getDescription()->shouldReturn('Petition description');
        $messageModel->getPetition()->getStatus()->shouldReturn('Opened');
        $messageModel->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $messageModel->getOwner()->getLogin()->shouldReturn('exampleUser');
        $messageModel->getContent()->shouldReturn('Example message content');
        
    }
    
}
