<?php declare(strict_types=1);

namespace App\Services\AttachmentCreator;

use Symfony\Component\HttpFoundation\File\File;
use App\Entity\{User, Attachment};

/**
 *  Take care about all process of creating attachment
 */
interface AttachmentCreatorInterface
{   
    /**
     * create Validate and create attachment object
     * @param   User            $user               User object whose is owner of attachment
     * @param   File            $file               Uploaded file object
     * @param   string          $fileType           String with type of attachment file e.g image
     * @param   string          $attachmentType     String with type of attachment e.g chat, petition
     * @throws  \Exception                          Throws \Exception when creating attachment fails
     * @return  Attachment                          Return attachment object 
     */
    public function create(User $user, File $file, string $fileType, string $attachmentType): Attachment;

}
