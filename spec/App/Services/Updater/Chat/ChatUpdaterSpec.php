<?php declare(strict_types=1);

namespace spec\App\Services\Updater\Chat;

use App\Services\Updater\Chat\{ChatUpdater, ChatUpdaterInterface};
use PhpSpec\ObjectBehavior;
use App\Model\Chat\ChatModel;
use App\Entity\Chat;

class ChatUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChatUpdater::class);
    }

    function it_implements_chat_updater_interface()
    {
        $this->shouldImplement(ChatUpdaterInterface::class);
    }

    function it_should_be_able_to_update_chat()
    {

        $chatModel = new ChatModel();
        $chatModel
            ->setTitle('Chat model title')
            ->setDescription('Chat model description')
            ;
        
        $chat = new Chat();
        $chat
            ->setTitle('Chat title')
            ->setDescription('Chat description')
            ;

        $chat = $this->update($chatModel, $chat);
        $chat->shouldBeAnInstanceOf(Chat::class);
        $chat->getTitle()->shouldReturn('Chat model title');
        $chat->getDescription()->shouldReturn('Chat model description');
    }
    
}
