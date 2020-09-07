<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Message;

use App\Services\Factory\Message\{PetitionMessageFactory, MessageFactoryInterface};
use App\Entity\{Message, User, Petition, MessageAttachment, PetitionMessage};
use App\Model\Message\MessageModel;
use PhpSpec\ObjectBehavior;

class PetitionMessageFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PetitionMessageFactory::class);
    }

    function it_implements_message_factory_interface()
    {
        $this->shouldImplement(MessageFactoryInterface::class);
    }

    function it_should_be_able_to_create_petition_message_without_attachments()
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

        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($user)
            ->setPetition($petition)
            ;

        $message = $this->create($messageModel);
        $message->shouldBeAnInstanceOf(PetitionMessage::class);
        $message->getPetition()->getTitle()->shouldReturn('Petition title');
        $message->getPetition()->getType()->shouldReturn('Ban');
        $message->getPetition()->getDescription()->shouldReturn('Petition description');
        $message->getPetition()->getStatus()->shouldReturn('Opened');
        $message->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $message->getOwner()->getLogin()->shouldReturn('exampleUser');
        $message->getContent()->shouldReturn('Example message content');
    }

    function it_should_be_able_to_create_petition_message_with_attachments()
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
            ->setPetition($petition)
            ->addAttachment($attachment)
            ;

        $message = $this->create($messageModel);
        $message->shouldBeAnInstanceOf(PetitionMessage::class);
        $message->getPetition()->getTitle()->shouldReturn('Petition title');
        $message->getPetition()->getType()->shouldReturn('Ban');
        $message->getPetition()->getDescription()->shouldReturn('Petition description');
        $message->getPetition()->getStatus()->shouldReturn('Opened');
        $message->getOwner()->getEmail()->shouldReturn('exampleuser@example.com');
        $message->getOwner()->getLogin()->shouldReturn('exampleUser');
        $message->getContent()->shouldReturn('Example message content');

        $attachments = $message->getAttachments();
        $attachments[0]->getUser()->shouldBeAnInstanceOf(User::class);
        $attachments[0]->getFilename()->shouldReturn('example.jpeg');
        $attachments[0]->getType()->shouldReturn('Image');
    }
    
}
