<?php declare(strict_types=1);

namespace App\Model\Message;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Entity\{User, Chat, Attachment};

class MessageModel
{
    
    private $id;

	/**
     * @Assert\NotBlank(message="Please enter a message")
     */
    private $content;

    /**
     * @Assert\NotBlank(message="Owner of message not found")
     */
    private $owner;

    /**
     * @Assert\NotBlank(message="Chat for this message not found")
     */
    private $chat;

    private $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return Collection|Attachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

        return $this;
    }

}
