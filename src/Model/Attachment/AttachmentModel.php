<?php declare(strict_types=1);

namespace App\Model\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UniqueProperties;
use App\Entity\{Message, Petition, User};

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
    const VALID_TYPES = ['Image', 'File'];

    private $id;

    private $message;

    private $petition;

    /**
     * @Assert\NotBlank(message="Owner cannot be blank.") 
     */
    private $user;

    /**
     * @Assert\NotBlank(message="Filename cannot be blank.")
     */
    private $filename;

    /**
     * @Assert\Choice(choices=AttachmentModel::VALID_TYPES, message="This is not a valid type.")
     */
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

    public function getPetition(): ?Petition
    {
        return $this->petition;
    }
    
    public function setPetition(?Petition $petition): self
    {
        $this->petition = $petition;

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
