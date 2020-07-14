<?php declare(strict_types=1);

namespace App\Services\Factory\Attachment;

use App\Model\Attachment\AttachmentModel;
use App\Entity\{Message, User, Attachment};

class AttachmentFactory implements AttachmentFactoryInterface 
{

    public function create(AttachmentModel $attachmentModel): Attachment
    {

        $attachment = new Attachment();
        $attachment
            ->setUser($attachmentModel->getUser())
            ->setMessage($attachmentModel->getMessage())
            ->setFilename($attachmentModel->getFilename())
            ;

        return $attachment;
    }

}
