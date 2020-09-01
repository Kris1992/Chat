<?php declare(strict_types=1);

namespace App\Services\AttachmentFileDeleter;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use App\Services\ImagesManager\ImagesConstants;

class AttachmentFileDeleter implements AttachmentFileDeleterInterface 
{

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /** @var FilesManagerInterface */
    private $filesManager;

    /**
     * AttachmentFileDeleter Constructor
     * 
     * @param ImagesManagerInterface    $attachmentImagesManager
     * @param FilesManagerInterface     $filesManager
     */
    public function __construct(ImagesManagerInterface $attachmentImagesManager, FilesManagerInterface $filesManager)  
    {
        $this->attachmentImagesManager = $attachmentImagesManager;
        $this->filesManager = $filesManager;
    }

    public function delete(string $subdirectory, string $filename, string $fileType, string $attachmentType): bool
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
                        throw new \Exception("Unsupported attachment type.");
                }

                return $this->attachmentImagesManager->deleteImage($filename, $directory);
            case 'File':
                switch ($attachmentType) {
                    case 'Chat':
                        $directory = AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::CHATS_FILES . '/' . $subdirectory;
                        break;
                    case 'Petition':
                        $directory = AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::PETITIONS_FILES . '/' . $subdirectory;
                        break;
                    default:
                        throw new \Exception("Unsupported attachment type.");
                }
                $filePath = $directory . '/' . $filename;
                return $this->filesManager->delete($filePath);
            default:
                throw new \Exception("Not supported attachment file to delete.");
        }
    }

}
