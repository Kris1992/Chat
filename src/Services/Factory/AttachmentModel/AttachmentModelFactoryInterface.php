<?php declare(strict_types=1);

namespace App\Services\Factory\AttachmentModel;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\Attachment\AttachmentModel;
use App\Entity\User;

/**
 *  Take care about creating message attachment model
 */
interface AttachmentModelFactoryInterface
{   
    /**
     * createFromData Create message or petition attachment model from data
     * @param   User            $user               User object whose is owner of message
     * @param   File            $file               Uploaded file object
     * @param   string          $fileType           String with type of attachment file e.g image
     * @param   string          $attachmentType     String with type of attachment e.g petition, chat
     * @return  AttachmentModel                     Return attachment model object
     * @throws  \Exception                          Throws an \Exception when upload file fails 
     */
    public function createFromData(User $user, File $file, string $fileType, string $attachmentType): AttachmentModel;

}
