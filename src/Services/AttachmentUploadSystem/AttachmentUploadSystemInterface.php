<?php declare(strict_types=1);

namespace App\Services\AttachmentUploadSystem;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\{Message, User, Attachment};

/**
 *  Take care about all process of upload attachment 
 */
interface AttachmentUploadSystemInterface
{   
    /**
     * upload Upload, validate and save message attachment object
     * @param   User            $user           User object whose is owner of message
     * @param   ?Message        $message        Message object which is owner of attachment or null
     * @param   Request         $request        Request with file object
     * @param   string          $type           String with type of attachment e.g image
     * @return  Attachment                      Return attachment object
     * @throws  \Exception                      Throws \Exception when upload attachment fails 
     */
    public function upload(User $user, ?Message $message, Request $request, string $type): Attachment;

}
