<?php declare(strict_types=1);

namespace App\Services\AttachmentCreator;

use App\Services\Factory\AttachmentModel\AttachmentModelFactoryInterface;
use App\Services\Factory\Attachment\AttachmentFactoryInterface;
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

    /** @var AttachmentFactoryInterface */
    private $attachmentFactory;

    /**
     * AttachmentCreator Constructor
     * 
     * @param ModelValidatorInterface $modelValidator
     * @param AttachmentModelFactoryInterface $attachmentModelFactory
     * @param AttachmentFactoryInterface $attachmentFactory
     */
    public function __construct(ModelValidatorInterface $modelValidator, AttachmentModelFactoryInterface $attachmentModelFactory, AttachmentFactoryInterface $attachmentFactory)  
    {
        $this->modelValidator = $modelValidator;
        $this->attachmentModelFactory = $attachmentModelFactory;
        $this->attachmentFactory = $attachmentFactory;
    }

    public function create(User $user, ?Message $message, File $file, string $type): Attachment
    {

        $fileModel = new AttachmentFileModel($file);

        switch ($type) {
            case 'Image':
                $isValid = $this->modelValidator->isValid($fileModel, ['attachment:image']);
                break;
            case 'File':
                $isValid = $this->modelValidator->isValid($fileModel, ['attachment:file']);
                break;
            default:
                throw new \Exception("Unsupported attachment type. Contact with admin.");
        }
        
        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());       
        }

        $attachmentModel = $this->attachmentModelFactory->createFromData($user, $message, $file, $type);
        $isValid = $this->modelValidator->isValid($attachmentModel);

        if (!$isValid) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }

        return $this->attachmentFactory->create($attachmentModel);

    }

}
