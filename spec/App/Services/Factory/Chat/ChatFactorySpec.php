<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Chat;

use App\Services\Factory\Chat\{ChatFactory, ChatFactoryInterface};
use App\Services\ImagesManager\ImagesManagerInterface;
use PhpSpec\ObjectBehavior;
use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

class ChatFactorySpec extends ObjectBehavior
{   
    function let(ImagesManagerInterface $attachmentImagesManager)
    {
        $this->beConstructedWith($attachmentImagesManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChatFactory::class);
    }

    function it_implements_chat_factory_interface()
    {
        $this->shouldImplement(ChatFactoryInterface::class);
    }

    function it_should_be_able_to_create_public_chat()
    {

        $user = new User();
        $user
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $chatModel = new ChatModel();
        $chatModel
            ->setTitle('Example chat title')
            ->setDescription('Example chat description')
            ->setOwner($user)
            ;

        $chat = $this->create($chatModel, $user, null);
        $chat->shouldBeAnInstanceOf(Chat::class);
        $chat->getTitle()->shouldReturn('Example chat title');
        $chat->getDescription()->shouldReturn('Example chat description');
        $chat->getIsPublic()->shouldReturn(true);
        $chat->getOwner()->shouldBeAnInstanceOf(User::class);
        $chat->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $chat->getOwner()->getLogin()->shouldReturn('exampleUser');

    }
    
}
