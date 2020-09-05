<?php declare(strict_types=1);

namespace App\Services\Factory\Attachment;

use App\Model\Attachment\AttachmentModel;
use App\Entity\{PetitionAttachment, Attachment};

class PetitionAttachmentFactory implements AttachmentFactoryInterface 
{

    public function create(AttachmentModel $attachmentModel): Attachment
    {

        $attachment = new PetitionAttachment();
        $attachment
            ->setUser($attachmentModel->getUser())
            ->setPetition($attachmentModel->getPetition())
            ->setFilename($attachmentModel->getFilename())
            ->setType($attachmentModel->getType())
            ;

        return $attachment;
    }

}
