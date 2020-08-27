<?php declare(strict_types=1);

namespace App\MessageHandler\Event;

use App\Services\AttachmentFileDeleter\AttachmentFileDeleterInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\Event\AttachmentDeletedEvent;

class RemoveAttachmentWhenAttachemtIsDeleted implements MessageHandlerInterface
{

    /** @var AttachmentFileDeleterInterface */
    private $attachmentFileDeleter;

    /**
     * RemoveAttachmentWhenAttachemtIsDeleted Constructor 
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
            $event->getType()
        );

        if (!$isRemoved) {
            throw new \Exception(sprintf("Cannot delete attachment: %s/%s", $event->getSubdirectory(), $event->getFilename()));
            
        }

    }
}
