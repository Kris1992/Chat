<?php declare(strict_types=1);

namespace App\Services\AttachmentFileDeleter;

use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use App\Services\AttachmentFileUploader\AttachmentsConstants;

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

    public function delete(string $subdirectory, string $filename, string $type): bool
    {
        switch ($type) {
            case 'Image':
                return $this->attachmentImagesManager->deleteImage($filename, $subdirectory);
            case 'File':
                $filePath = AttachmentsConstants::CHATS_FILES.'/'.$subdirectory.'/'.$filename;
                return $this->filesManager->delete($filePath);
            default:
                throw new \Exception("Not supported attachment file to delete");
        }
    }

}
