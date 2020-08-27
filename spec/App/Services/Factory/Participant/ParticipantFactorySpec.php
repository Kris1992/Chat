<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Participant;

use App\Services\Factory\Participant\{ParticipantFactory, ParticipantFactoryInterface};
use App\Entity\{Chat, User, Participant, ParticipateTime};
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class ParticipantFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParticipantFactory::class);
    }

    function it_implements_participant_factory_interface()
    {
        $this->shouldImplement(ParticipantFactoryInterface::class);
    }

    function it_should_be_able_to_create_participant()
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

        $participant = $this->create($user, $chat);
        $participant->shouldBeAnInstanceOf(Participant::class);
        $participant->getChat()->getTitle()->shouldReturn('Chat title');
        $participant->getChat()->getDescription()->shouldReturn('Chat description');
        $participant->getUser()->getEmail()->shouldReturn('exampleuser@example.com');
        $participant->getUser()->getLogin()->shouldReturn('exampleUser');
        $participant->getIsRemoved()->shouldReturn(false);

        $participant->getParticipateTimes()->shouldBeAnInstanceOf(ArrayCollection::class);
        $participateTimes = $participant->getParticipateTimes();
        $participateTimes[0]->shouldBeAnInstanceOf(ParticipateTime::class);
        $participateTimes[0]->getParticipant()->shouldBeAnInstanceOf(Participant::class);
        $participateTimes[0]->getStartAt()->shouldReturnAnInstanceOf('\DateTime');
        $participateTimes[0]->getStopAt()->shouldReturn(null);
        $participateTimes[1]->shouldReturn(null);

    }

}
