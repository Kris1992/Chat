<?php declare(strict_types=1);

namespace App\MessageHandler\Command;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\Command\CheckIsAttachmentUsed;
use App\Repository\AttachmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\Event\AttachmentDeletedEvent;
use Psr\Log\LoggerInterface;
use App\Entity\{MessageAttachment, PetitionAttachment};

class CheckIsAttachmentUsedHandler implements  MessageSubscriberInterface
{

    /** @var MessageBusInterface */
    private $eventBus;

    /** @var AttachmentRepository */
    private $attachmentRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger;

    /**
     * CheckIsAttachmentUsedHandler Constructor 
     * @param MessageBusInterface       $eventBus
     * @param AttachmentRepository      $attachmentRepository
     * @param EntityManagerInterface    $entityManager
     * @param LoggerInterface           $logger
     */
    public function __construct(MessageBusInterface $eventBus, AttachmentRepository $attachmentRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->attachmentRepository = $attachmentRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function __invoke(CheckIsAttachmentUsed $checkIsAttachmentUsed)
    {

        $attachmentId = $checkIsAttachmentUsed->getId();
        $attachment = $this->attachmentRepository->findOneBy(['id' => $attachmentId]);

        if(!$attachment) {
            throw new \Exception("Cannot find given attachment.");
        }

        if (($attachment instanceof MessageAttachment && $attachment->getMessage())  
            || ($attachment instanceof PetitionAttachment && $attachment->getPetition())) {
            
            if($this->logger) {
                $this->logger->info(sprintf('Attachment with ID: %d was used.', $attachmentId));
            }

        } else {
            
            $filename = $attachment->getFilename();
            $fileType = $attachment->getType();

            $this->entityManager->remove($attachment);
            $this->entityManager->flush();

            $this->eventBus->dispatch(new AttachmentDeletedEvent(
                $checkIsAttachmentUsed->getSubdirectory(), 
                $filename, 
                $fileType,
                $checkIsAttachmentUsed->getAttachmentType()
            ));
        }
    }

    public static function getHandledMessages(): iterable
    {
        yield CheckIsAttachmentUsed::class => [
            'method' => '__invoke',
        ];
    }
}
