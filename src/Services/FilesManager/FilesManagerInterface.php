<?php
declare(strict_types=1);

namespace App\Services\FilesManager;

use Symfony\Component\HttpFoundation\File\File;

/**
 *  Manage Files which are not images(upload, delete, treatment)
 */
interface FilesManagerInterface
{   

    /**
     * upload Upload file on the server
     * @param File   $file          Uploaded file
     * @param string $folderName    Name of folder (where save file?)
     * @throws Exception            Throw an exception when save file fails
     * @return string               Return new filename
     */
    public function upload(File $file, string $folderName): string;


}
