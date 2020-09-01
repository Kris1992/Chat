<?php declare(strict_types=1);

namespace App\Services\AttachmentFileUploader;

use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use App\Services\ImagesManager\ImagesConstants;
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

    public function upload(File $file, string $subdirectory, string $fileType, string $attachmentType): ?string
    {
        switch ($fileType) {
            case 'Image':
                switch ($attachmentType) {
                    case 'Chat':
                        $directory = ImagesConstants::CHATS_IMAGES . '/' . $subdirectory;
                        break;
                    case 'Petition':
                        $directory = ImagesConstants::PETITIONS_IMAGES . '/' . $subdirectory;
                        break;
                    default:
                        throw new \Exception("Unsupported attachment type. Contact with admin.");
                }

                return $this->attachmentImagesManager->uploadImage($file, null, $directory);
            case 'File':
                switch ($attachmentType) {
                    case 'Chat':
                        $directory = AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::CHATS_FILES . '/' . $subdirectory;
                        break;
                    case 'Petition':
                        $directory = AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::PETITIONS_FILES . '/' . $subdirectory;
                        break;
                    default:
                        throw new \Exception("Unsupported attachment type. Contact with admin.");
                }
                
                return $this->filesManager->upload($file, $directory);

            default:
                throw new \Exception("Unsupported attachment file type. Contact with admin.");
        }

    }

}
