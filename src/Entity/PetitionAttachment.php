<?php declare(strict_types=1);

namespace App\Entity;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\PetitionAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetitionAttachmentRepository::class)
 */
class PetitionAttachment extends Attachment
{

    /**
     * @ORM\ManyToOne(targetEntity=Petition::class, inversedBy="attachments")
     */
    private $petition;

    public function getPetition(): ?Petition
    {
        return $this->petition;
    }

    public function setPetition(?Petition $petition): self
    {
        $this->petition = $petition;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES . '/' . ImagesConstants::PETITIONS_IMAGES . '/' . $this->user->getLogin() . '/' . $this->getFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES . '/' . ImagesConstants::PETITIONS_IMAGES . '/' . $this->user->getLogin() . '/' . ImagesConstants::THUMB_IMAGES . '/' . $this->getFilename();
    }

    public function getFilePath(): ?string
    {
        return AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::PETITIONS_FILES . '/' . $this->user->getLogin() . '/' . $this->getFilename();
    }
}
