<?php declare(strict_types=1);

namespace App\Services\AttachmentFileDeleter;

use App\Services\ImagesManager\ImagesManagerInterface;

class AttachmentFileDeleter implements AttachmentFileDeleterInterface 
{

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /**
     * AttachmentFileDeleter Constructor
     * 
     * @param ImagesManagerInterface $attachmentImagesManager
     */
    public function __construct(ImagesManagerInterface $attachmentImagesManager)  
    {
        $this->attachmentImagesManager = $attachmentImagesManager;
    }

    public function delete(string $subdirectory, string $filename, string $type): bool
    {
        switch ($type) {
            case 'Image':
                return $this->attachmentImagesManager->deleteImage($filename, $subdirectory);
            default:
                return false;
        }
    }

}
