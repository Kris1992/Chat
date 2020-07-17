<?php declare(strict_types=1);

namespace App\Model\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UniqueProperties;
use App\Entity\{Message, User};

/**
 * @UniqueProperties(
 *     fields={"filename", "user"},
 *     errorPath="filename",
 *     entityClass="Attachment",
 *     message="File with this name is already uploaded.Please change name of file."
 * )
 */
class AttachmentModel
{
    
    private $id;

    private $message;

    /**
     * @Assert\NotBlank(message="Owner cannot be blank.") 
     */
    private $user;

    /**
     * @Assert\NotBlank(message="Filename cannot be blank.")
     */
    private $filename;

    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }
    
    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

}
