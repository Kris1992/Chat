<?php declare(strict_types=1);

namespace App\Services\ImagesManager;

use Symfony\Component\HttpFoundation\File\File;

/**
 *  Manage Images (upload, delete, change)
 */
interface ImagesManagerInterface
{   

    /**
     * uploadImage Upload image and compress it to smaller one thumb image if it is too large
     * @param  File         $file             Uploaded file
     * @param  string|null  $existingFilename Filename of image which was uploaded before[optional]
     * @param  string|null  $subdirectory     Subdirectory for image[optional]
     * @param  integer      $newWidth         Width of compressed image [optional]
     * @return string|null                    Return new filename or null if upload fails
     */
    public function uploadImage(File $file, ?string $existingFilename, ?string $subdirectory, int $newWidth): ?string;

    /**
     * deleteImage Delete images (original and compressed) from server 
     * @param  string   $existingFilename Filename of image to delete
     * @param  string   $subdirectory     Subdirectory for image[optional]
     * @return bool                       Return true if success otherwise false
     */
    public function deleteImage(string $existingFilename, ?string $subdirectory): bool;

}
