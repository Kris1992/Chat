<?php declare(strict_types=1);

namespace App\Services\AttachmentFileDeleter;

/**
 *  Take care about all process of delete attachment file
 */
interface AttachmentFileDeleterInterface
{   

    /**
     * delete Delete attachment file
     * @param  string       $subdirectory       String with subdirectory
     * @param  string       $filename           String with filename
     * @param  string       $fileType           String with type of attachment file e.g Image
     * @param  string       $attachmentType     String with type of attachment e.g Chat, Petition
     * @throws \Exception                       Throws an \Exception when type is not supported
     * @return bool                             Return true if deleted otherwise return false
     */
    public function delete(string $subdirectory, string $filename, string $fileType, string $attachmentType): bool;

}
