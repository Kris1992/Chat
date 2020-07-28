<?php declare(strict_types=1);

namespace App\Services\AttachmentFileUploader;

use Symfony\Component\HttpFoundation\File\File;

/**
 *  Take care about all process of upload attachment file
 */
interface AttachmentFileUploaderInterface
{   

    /**
     * upload Upload attachment file
     *
     * @param   File        $file           Uploaded file 
     * @param   string      $subdirectory   String with subdirectory
     * @param   string      $type           String with type of attachment e.g Image, File
     * @return  string|null                 Return new filename or null if upload fails
     * @throws  Exception                   Throws Exception when uploaded file type is not supported
     */
    public function upload(File $file, string $subdirectory, string $type): ?string;

}
