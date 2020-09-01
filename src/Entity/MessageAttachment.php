<?php declare(strict_types=1);

namespace App\Entity;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\MessageAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageAttachmentRepository::class)
 */
class MessageAttachment extends Attachment
{

    /**
     * @ORM\ManyToOne(targetEntity=Message::class, inversedBy="attachments")
     */
    private $message;

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES . '/' . ImagesConstants::CHATS_IMAGES . '/' . $this->user->getLogin() . '/' . $this->getFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES . '/' . ImagesConstants::CHATS_IMAGES . '/' . $this->user->getLogin() . '/' . ImagesConstants::THUMB_IMAGES . '/' . $this->getFilename();
    }

    public function getFilePath(): ?string
    {
        return AttachmentsConstants::ATTACHMENTS_FILES . '/' . AttachmentsConstants::CHATS_FILES . '/' . $this->user->getLogin() . '/' . $this->getFilename();
    }

}
