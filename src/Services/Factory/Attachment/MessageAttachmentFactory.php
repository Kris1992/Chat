<?php declare(strict_types=1);

namespace App\Services\Factory\Attachment;

use App\Model\Attachment\AttachmentModel;
use App\Entity\{MessageAttachment, Attachment};

class MessageAttachmentFactory implements AttachmentFactoryInterface 
{

    public function create(AttachmentModel $attachmentModel): Attachment
    {

        $attachment = new MessageAttachment();
        $attachment
            ->setUser($attachmentModel->getUser())
            ->setMessage($attachmentModel->getMessage())
            ->setFilename($attachmentModel->getFilename())
            ->setType($attachmentModel->getType())
            ;

        return $attachment;
    }

}
