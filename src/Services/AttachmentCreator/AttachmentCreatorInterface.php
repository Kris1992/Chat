<?php declare(strict_types=1);

namespace App\Services\AttachmentCreator;

use Symfony\Component\HttpFoundation\File\File;
use App\Entity\{Message, User, Attachment};

/**
 *  Take care about all process of creating message attachment
 */
interface AttachmentCreatorInterface
{   
    /**
     * create Create message attachment object
     * @param   User            $user               User object whose is owner of message
     * @param   ?Message        $message            Message object which is owner of attachment or null
     * @param   File            $file               File object
     * @param   string          $fileType           String with type of attachment file e.g image
     * @param   string          $attachmentType     String with type of attachment e.g chat, petition
     * @return  Attachment                          Return attachment object
     * @throws  \Exception                          Throws \Exception when creating attachment fails 
     */
    public function create(User $user, ?Message $message, File $file, string $fileType, string $attachmentType): Attachment;

}
