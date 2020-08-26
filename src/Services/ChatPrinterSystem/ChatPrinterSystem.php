<?php declare(strict_types=1);

namespace App\Services\ChatPrinterSystem;

use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Message\Command\RemoveScreenFile;
use App\Services\ChatPrinter\ChatPrinter;
use App\Entity\{User, Chat};

class ChatPrinterSystem implements ChatPrinterSystemInterface 
{

    /** @var ChatPrinter */
    private $chatPrinter;

    /** @var MessageBusInterface */
    private $messageBus;

    /**
     * ChatPrinterSystem Constructor
     * 
     * @param ChatPrinter $chatPrinter
     * @param MessageBusInterface $messageBus
     */
    public function __construct(ChatPrinter $chatPrinter, MessageBusInterface $messageBus)  
    {
        $this->chatPrinter = $chatPrinter;
        $this->messageBus = $messageBus;
    }

    public function printToFile(Chat $chat, User $currentUser, \DateTimeInterface $startAt, \DateTimeInterface $stopAt, string $fileFormat): string
    {

        $messages = $chat->getMessagesBetween($startAt, $stopAt);

        if ($messages->isEmpty()) {
            throw new \Exception("There is no messages to print in given interval.");
        }

        $concretePrinter = $this->chatPrinter->choosePrinter($fileFormat);
        $link = $concretePrinter->printToFile($messages, $currentUser, $startAt, $stopAt);

        $message = new RemoveScreenFile(basename($link));
        $envelope = new Envelope($message, [
            new DelayStamp(900000)//15 minutes delay 
        ]);
        $this->messageBus->dispatch($envelope);

        return $link;
    }

}
