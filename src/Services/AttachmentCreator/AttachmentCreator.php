<?php declare(strict_types=1);

namespace App\Services\AttachmentCreator;

use App\Services\Factory\AttachmentModel\AttachmentModelFactoryInterface;
use App\Services\Factory\Attachment\AttachmentFactory;
use App\Model\Attachment\AttachmentFileModel;
use App\Services\ModelValidator\ModelValidatorInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Entity\{Message, User, Attachment};

class AttachmentCreator implements AttachmentCreatorInterface 
{

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var AttachmentModelFactoryInterface */
    private $attachmentModelFactory;

    /**
     * AttachmentCreator Constructor
     * 
     * @param ModelValidatorInterface $modelValidator
     * @param AttachmentModelFactoryInterface $attachmentModelFactory
     */
    public function __construct(ModelValidatorInterface $modelValidator, AttachmentModelFactoryInterface $attachmentModelFactory)  
    {
        $this->modelValidator = $modelValidator;
        $this->attachmentModelFactory = $attachmentModelFactory;
    }

    public function create(User $user, ?Message $message, File $file, string $fileType, string $attachmentType): Attachment
    {

        $fileModel = new AttachmentFileModel($file);

        switch ($fileType) {
            case 'Image':
                $isValid = $this->modelValidator->isValid($fileModel, ['attachment:image']);
                break;
            case 'File':
                $isValid = $this->modelValidator->isValid($fileModel, ['attachment:file']);
                break;
            default:
                throw new \Exception("Unsupported attachment file type. Contact with admin.");
        }
        
        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());       
        }

        $attachmentModel = $this->attachmentModelFactory->createFromData($user, $message, $file, $fileType, $attachmentType);
        $isValid = $this->modelValidator->isValid($attachmentModel);

        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }

        $attachmentFactory = AttachmentFactory::chooseFactory($attachmentType);
        
        return $attachmentFactory->create($attachmentModel);

    }

}
