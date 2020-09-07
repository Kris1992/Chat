<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Petition;

use App\Services\Factory\Petition\{PetitionFactory, PetitionFactoryInterface};
use App\Services\AttachmentHelper\AttachmentHelperInterface;
use App\Model\Petition\PetitionModel;
use App\Entity\{Petition, User};
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PetitionFactorySpec extends ObjectBehavior
{
    function let(AttachmentHelperInterface $attachmentHelper)
    {
        $this->beConstructedWith($attachmentHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PetitionFactory::class);
    }

    function it_implements_petition_factory_interface()
    {
        $this->shouldImplement(PetitionFactoryInterface::class);
    }

    function it_should_be_able_to_create_petition_without_attachments()
    {
        $petitioner = new User();
        $petitioner
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $petitionModel = new PetitionModel();
        $petitionModel
            ->setTitle('Petition title')
            ->setDescription('Petition description')
            ->setType('Ban')
            ->setPetitioner($petitioner)
            ;

        $petition = $this->create($petitionModel, $petitioner);
        $petition->shouldBeAnInstanceOf(Petition::class);
        $petition->getTitle()->shouldReturn('Petition title');
        $petition->getDescription()->shouldReturn('Petition description');
        $petition->getType()->shouldReturn('Ban');
        $petition->getPetitioner()->shouldBeAnInstanceOf(User::class);
        $petition->getPetitioner()->getEmail()->shouldReturn('exampleuser@example.com');
        $petition->getPetitioner()->getLogin()->shouldReturn('exampleUser');

    }

}