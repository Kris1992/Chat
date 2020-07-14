<?php declare(strict_types=1);

namespace App\Services\AttachmentManager;

use Symfony\Component\HttpFoundation\File\File;
use App\Entity\{Message, User, Attachment};

/**
 *  Take care about all process of creating message attachment
 */
interface AttachmentManagerInterface
{   
    /**
     * create Create message attachment object
     * @param   User            $user           User object whose is owner of message
     * @param   Message         $message        Message object which is owner of attachment
     * @param   File            $file           File object
     * @param   string          $type           String with type of attachment e.g image
     * @return  Attachment                      Return attachment object
     * @throws  \Exception                      Throws \Exception when creating attachment fails 
     */
    public function create(User $user, ?Message $message, File $file, string $type): Attachment;

}
