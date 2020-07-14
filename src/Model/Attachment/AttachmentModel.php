<?php declare(strict_types=1);

namespace App\Model\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\{Message, User};

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

}
