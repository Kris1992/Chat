<?php
declare(strict_types=1);

namespace App\MessageHandler\Command;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Message\Command\CheckUserActivityOnPublicChat;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckUserActivityOnPublicChatHandler implements  MessageSubscriberInterface
{

    /** @var MessageBusInterface */
    private $messageBus;

    /** @var ParticipantRepository */
    private $participantRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(MessageBusInterface $messageBus, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $this->messageBus = $messageBus;
        $this->participantRepository = $participantRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(CheckUserActivityOnPublicChat $checkUserActivityOnPublicChat)
    {
        $participantId = $checkUserActivityOnPublicChat->getParticipantId();

        $participant = $this->participantRepository->findOneBy(['id' => $participantId]);

        if(!$participant) {
            throw new \Exception("Cannot find participant of chat.");
        }

        if ($participant->getLastSeenAt() > new \DateTime('now -3 minutes')) {
            $message = new CheckUserActivityOnPublicChat($participantId);
            $envelope = new Envelope($message, [
                new DelayStamp(120000)//2 minutes delay 
            ]);
            $this->messageBus->dispatch($envelope);
        } else {
            $this->entityManager->remove($participant);
            $this->entityManager->flush();
        }
        
    }

    public static function getHandledMessages(): iterable
    {
        yield CheckUserActivityOnPublicChat::class => [
            'method' => '__invoke',
        ];
    }
}
