<?php declare(strict_types=1);

namespace App\Services\AttachmentUploadSystem;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\{User, Attachment};

/**
 *  Take care about all process of upload attachment 
 */
interface AttachmentUploadSystemInterface
{   
    /**
     * upload Upload, validate and save message attachment object
     * @param   User            $user               User object whose is owner of attachment
     * @param   Request         $request            Request with file object
     * @param   string          $fileType           String with type of attachment file e.g image
     * @return  Attachment                          Return attachment object
     * @throws  \Exception                          Throws \Exception when upload attachment fails 
     */
    public function upload(User $user, Request $request, string $fileType): Attachment;

}
