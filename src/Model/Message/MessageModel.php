<?php declare(strict_types=1);

namespace App\Model\Message;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Entity\{User, Chat, MessageAttachment, Petition};

class MessageModel
{
    
    private $id;

	/**
     * @Assert\NotBlank(message="Please enter a message", groups={"chat:message", "petition:message"})
     */
    private $content;

    /**
     * @Assert\NotBlank(message="Owner of message not found", groups={"chat:message", "petition:message"})
     */
    private $owner;

    /**
     * @Assert\NotBlank(message="Chat for this message not found", groups={"chat:message"})
     * @Assert\IsNull(message="Invalid type of message", groups={"petition:message"})
     */
    private $chat;

    /**
     * @Assert\NotBlank(message="Petition for this message not found", groups={"petition:message"})
     * @Assert\IsNull(message="Invalid type of message", groups={"chat:message"})
     */
    private $petition;

    private $readedAt;

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
     * @return Collection|MessageAttachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(MessageAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
        }

        return $this;
    }

    public function removeAttachment(MessageAttachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

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

    public function getReadedAt(): ?\DateTimeInterface
    {
        return $this->readedAt;
    }

    public function setReadedAt(?\DateTimeInterface $readedAt): self
    {
        $this->readedAt = $readedAt;

        return $this;
    }

}
