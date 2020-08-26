<?php declare(strict_types=1);

namespace App\Services\AttachmentHelper;

use App\Entity\User;

/**
 *  Provide helpers methods to attachments
 */
interface AttachmentHelperInterface
{   

    /**
     * getAttachments Get array of attachments filenames from content string
     * @param   ?string      $content       String with content of message or null
     * @return  array|null                  Return array of attachments filenames or null
     */
    public function getAttachmentsFilenames(?string $content): ?array;
    
    /**
     * getAttachments Get array of attachment objects from filenames  
     * @param  array        $filenames      Array with attachments filenames
     * @param  User         $user           User object whose is owner of message
     * @return array                        Array with Attachment objects or empty array
     */
    public function getAttachments(array $filenames, User $user): array;

}
