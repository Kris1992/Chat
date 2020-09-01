<?php declare(strict_types=1);

namespace App\Services\Factory\AttachmentModel;

use App\Services\AttachmentFileUploader\AttachmentFileUploaderInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Model\Attachment\AttachmentModel;
use App\Entity\{Message, User};

class AttachmentModelFactory implements AttachmentModelFactoryInterface 
{

    /** @var AttachmentFileUploaderInterface */
    private $attachmentFileUploader;

    /**
     * AttachmentModelFactory Constructor
     * 
     * @param AttachmentFileUploaderInterface $attachmentFileUploader
     */
    public function __construct(AttachmentFileUploaderInterface $attachmentFileUploader)  
    {
        $this->attachmentFileUploader = $attachmentFileUploader;
    }

    public function createFromData(User $user, ?Message $message, File $file, string $fileType, string $attachmentType): AttachmentModel
    {

        $filename = $this->attachmentFileUploader->upload($file, $user->getLogin(), $fileType, $attachmentType);

        if (!$filename) {
            throw new \Exception("Cannot upload that file.");
        }

        $attachmentModel = new AttachmentModel();
        $attachmentModel
            ->setUser($user)
            ->setMessage($message)
            ->setFilename($filename)
            ->setType($fileType)
            ;

        return $attachmentModel;
    }

}
