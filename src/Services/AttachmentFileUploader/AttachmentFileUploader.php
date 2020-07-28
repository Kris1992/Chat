<?php declare(strict_types=1);

namespace App\Services\AttachmentFileUploader;

use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentFileUploader implements AttachmentFileUploaderInterface 
{

    /** @var FilesManagerInterface */
    private $filesManager;

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /**
     * AttachmentFileUploader Constructor
     *
     *@param FilesManagerInterface $filesManager
     *@param ImagesManagerInterface $attachmentImagesManager
     *
     */
    public function __construct(FilesManagerInterface $filesManager, ImagesManagerInterface $attachmentImagesManager)
    {
        $this->filesManager = $filesManager;
        $this->attachmentImagesManager = $attachmentImagesManager;
    }

    public function upload(File $file, string $subdirectory, string $type): ?string
    {
        switch ($type) {
            case 'Image':
                return $this->attachmentImagesManager->uploadImage($file, null, $subdirectory);
            case 'File':
                $directory =  AttachmentsConstants::CHATS_FILES.'/'.$subdirectory;
                return $this->filesManager->upload($file, $directory);

            default:
                throw new \Exception("Unsupported attachment type. Contact with admin.");
        }

    }

}
