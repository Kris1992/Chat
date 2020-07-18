<?php declare(strict_types=1);

namespace spec\App\Services\Factory\ChatModel;

use App\Services\Factory\ChatModel\{ChatModelFactory, ChatModelFactoryInterface};
use App\Services\Factory\Participant\ParticipantFactoryInterface;
use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};
use PhpSpec\ObjectBehavior;

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
            ->setOwner($user)
            ;

        $chatModel = $this->create($chat);
        $chatModel->shouldBeAnInstanceOf(ChatModel::class);
        $chatModel->getTitle()->shouldReturn('Example chat title');
        $chatModel->getDescription()->shouldReturn('Example chat description');
        $chatModel->getIsPublic()->shouldReturn(true);
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

}
