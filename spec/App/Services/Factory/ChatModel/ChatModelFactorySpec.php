<?php declare(strict_types=1);

namespace spec\App\Services\Factory\ChatModel;

use App\Services\Factory\ChatModel\{ChatModelFactory, ChatModelFactoryInterface};
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use App\Entity\{Chat, User, Participant};
use App\Model\Chat\ChatModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChatModelFactorySpec extends ObjectBehavior
{

    function let(ParticipantFactoryInterface $participantFactory)
    {
        $this->beConstructedWith($participantFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChatModelFactory::class);
    }

    function it_implements_chat_model_factory_interface()
    {
        $this->shouldImplement(ChatModelFactoryInterface::class);
    }

    function it_should_be_able_to_create_chat_model()
    {

        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $chat = new Chat();
        $chat
            ->setTitle('Example chat title')
            ->setDescription('Example chat description')
            ->setIsPublic(true)
            ->setImageFilename('image.jpg')
            ->setOwner($user)
            ;

        $chatModel = $this->create($chat);
        $chatModel->shouldBeAnInstanceOf(ChatModel::class);
        $chatModel->getTitle()->shouldReturn('Example chat title');
        $chatModel->getDescription()->shouldReturn('Example chat description');
        $chatModel->getIsPublic()->shouldReturn(true);
        $chatModel->getImageFilename()->shouldReturn('image.jpg');
        $chatModel->getOwner()->shouldBeAnInstanceOf(User::class);
        $chatModel->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $chatModel->getOwner()->getLogin()->shouldReturn('exampleUser');

    }

    function it_should_be_able_to_create_public_chat_model_from_data()
    {

        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $chatModel = $this->createFromData($user, true, null, 'Example chat title', 'Example chat description');
        $chatModel->shouldBeAnInstanceOf(ChatModel::class);
        $chatModel->getTitle()->shouldReturn('Example chat title');
        $chatModel->getDescription()->shouldReturn('Example chat description');
        $chatModel->getIsPublic()->shouldReturn(true);
        $chatModel->getOwner()->shouldBeAnInstanceOf(User::class);
        $chatModel->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $chatModel->getOwner()->getLogin()->shouldReturn('exampleUser');

    }

    function it_should_be_able_to_create_private_chat_model_from_data($participantFactory)
    {

        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $participantUser = new User();
        $participantUser
            ->setEmail('participantUser@example.com')
            ->setLogin('participantUser')
            ;

        $participant = new Participant();
        $participant
            ->setUser($participantUser)
            ;

        $participantFactory->create(Argument::any(), null)->shouldBeCalledTimes(2)->willReturn($participant);
        $chatModel = $this->createFromData($user, false, [$participantUser], 'Example chat title', 'Example chat description');
        $chatModel->shouldBeAnInstanceOf(ChatModel::class);
        $chatModel->getTitle()->shouldReturn('Conversation');
        $chatModel->getDescription()->shouldReturn('Example chat description');
        $chatModel->getIsPublic()->shouldReturn(false);
        $chatModel->getOwner()->shouldBeAnInstanceOf(User::class);
        $chatModel->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $chatModel->getOwner()->getLogin()->shouldReturn('exampleUser');
        $participants = $chatModel->getParticipants();
        $participants[0]->getUser()->shouldBeAnInstanceOf(User::class);
        $participants[0]->getUser()->getEmail()->shouldReturn('participantUser@example.com');
        $participants[0]->getUser()->getLogin()->shouldReturn('participantUser');

    }

}
