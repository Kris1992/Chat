<?php declare(strict_types=1);

namespace App\MessageHandler\Event;

use App\Services\AttachmentFileDeleter\AttachmentFileDeleterInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\Event\AttachmentDeletedEvent;

class RemoveAttachmentFileWhenAttachmentIsDeleted implements MessageHandlerInterface
{

    /** @var AttachmentFileDeleterInterface */
    private $attachmentFileDeleter;

    /**
     * RemoveAttachmentFileWhenAttachmentIsDeleted Constructor 
     * @param AttachmentFileDeleterInterface       $attachmentFileDeleter
     */
    public function __construct(AttachmentFileDeleterInterface $attachmentFileDeleter)
    {
        $this->attachmentFileDeleter = $attachmentFileDeleter;
    }

    public function __invoke(AttachmentDeletedEvent $event)
    {
        $isRemoved = $this->attachmentFileDeleter->delete(
            $event->getSubdirectory(),
            $event->getFilename(),
            $event->getFileType(),
            $event->getAttachmentType()
        );

        if (!$isRemoved) {
            throw new \Exception(sprintf("Cannot delete attachment: %s/%s", $event->getSubdirectory(), $event->getFilename()));
            
        }

    }
}
