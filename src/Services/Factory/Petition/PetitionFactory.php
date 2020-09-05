<?php declare(strict_types=1);

namespace App\Services\Factory\Petition;

use App\Services\AttachmentHelper\AttachmentHelperInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Model\Petition\PetitionModel;
use App\Entity\{Petition, User};

class PetitionFactory implements PetitionFactoryInterface 
{

    /** @var AttachmentHelperInterface */
    private $attachmentHelper;

    /**
     * PetitionFactory Constructor
     * 
     * @param AttachmentHelperInterface $attachmentHelper
     */
    public function __construct(AttachmentHelperInterface $attachmentHelper)  
    {
        $this->attachmentHelper = $attachmentHelper;
    }

    public function create(PetitionModel $petitionModel, User $petitioner): Petition
    {

        $petition = new Petition();
        $petition
            ->setTitle($petitionModel->getTitle())
            ->setDescription($petitionModel->getDescription())
            ->setType($petitionModel->getType())
            ->setPetitioner($petitioner)
            ->setStatus('Pending')
            ;
        
        $attachments = $this->attachmentHelper->getAttachments(
            $petitionModel->getAttachementsFilenames(),
            $petitioner
        );
        
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $petition->addAttachment($attachment);  
            }
        }
        
        return $petition;
    }

}
