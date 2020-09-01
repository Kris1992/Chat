<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"message" = "Message", "petitionMessage" = "PetitionMessage",  "chatMessage" = "ChatMessage"})
 */
abstract class Message
{
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"chat:message", "chat:list"})
     */
    protected $owner;

    /**
     * @ORM\Column(type="text")
     * @Groups({"chat:message", "chat:list"})
     */
    protected $content;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"chat:message", "chat:list"})
     */
    protected $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=MessageAttachment::class, mappedBy="message", orphanRemoval=true, cascade={"persist", "refresh", "remove"})
     */
    protected $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * getSanitazedContent Get message content without images and attachments
     * @return string
     * @Groups({"chat:message", "chat:list"})
     */
    public function getSanitazedContent(): ?string
    {
        $content = $this->getContent();

        $pattern = '~< *img[^>]*src *= *["\']?([^"\']*)|<a class="uploaded-file"[^>]*href *= *["\']?([^"\']*)~';
        preg_match_all($pattern, $content, $matches);

        if ($matches[1] && $matches[2]) {
            return '<span class="fas fa-file-alt"></span> Sent file and image.';
        } else if ($matches[1]) {
            return '<span class="fas fa-file-image"></span> Sent image.';
        } else if ($matches[2]) {
            return '<span class="fas fa-file-alt"></span> Sent file.';
        }
        
        
        return $content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
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
            $attachment->setMessage($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getMessage() === $this) {
                $attachment->setMessage(null);
            }
        }

        return $this;
    }

}
