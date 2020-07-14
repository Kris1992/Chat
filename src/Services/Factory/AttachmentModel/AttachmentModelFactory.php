<?php
declare(strict_types=1);

namespace App\Services\Factory\AttachmentModel;

use App\Services\ImagesManager\ImagesManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Model\Attachment\AttachmentModel;
use App\Entity\{Message, User};

class AttachmentModelFactory implements AttachmentModelFactoryInterface 
{

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /**
     * AttachmentModelFactory Constructor
     * 
     * @param ImagesManagerInterface $attachmentImagesManager
     */
    public function __construct(ImagesManagerInterface $attachmentImagesManager)  
    {
        $this->attachmentImagesManager = $attachmentImagesManager;
    }

    public function createFromData(User $user, ?Message $message, File $file, string $type): AttachmentModel
    {

        switch ($type) {
            case 'Image':
                $filename = $this->attachmentImagesManager->uploadImage($file, null, $user->getLogin());
                break;
            default:
                throw new \Exception("Unsupported attachment type. Contact with admin.");
        }

        if (!$filename) {
            throw new \Exception("Cannot upload that file.");
        }

        $attachmentModel = new AttachmentModel();
        $attachmentModel
            ->setUser($user)
            ->setMessage($message)
            ->setFilename($filename)
            ;

        return $attachmentModel;
    }

}
