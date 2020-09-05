<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Model\Petition\PetitionConstants;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetitionRepository::class)
 */
class Petition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="petitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $petitioner;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=PetitionMessage::class, mappedBy="petition", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $petitionMessages;

    /**
     * @ORM\OneToMany(targetEntity=PetitionAttachment::class, mappedBy="petition", orphanRemoval=true)
     */
    private $attachments;

    public function __construct()
    {
        $this->petitionMessages = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * getTypeDescription Get full description of given type
     * @return string
     */
    public function getTypeDescription(): ?string
    {
        return array_keys(PetitionConstants::TYPES_DESC, $this->type, true)[0];
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPetitioner(): ?User
    {
        return $this->petitioner;
    }

    public function setPetitioner(?User $petitioner): self
    {
        $this->petitioner = $petitioner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|PetitionMessage[]
     */
    public function getPetitionMessages(): Collection
    {
        return $this->petitionMessages;
    }

    public function addPetitionMessage(PetitionMessage $petitionMessage): self
    {
        if (!$this->petitionMessages->contains($petitionMessage)) {
            $this->petitionMessages[] = $petitionMessage;
            $petitionMessage->setPetition($this);
        }

        return $this;
    }

    public function removePetitionMessage(PetitionMessage $petitionMessage): self
    {
        if ($this->petitionMessages->contains($petitionMessage)) {
            $this->petitionMessages->removeElement($petitionMessage);
            // set the owning side to null (unless already changed)
            if ($petitionMessage->getPetition() === $this) {
                $petitionMessage->setPetition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PetitionAttachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(PetitionAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setPetition($this);
        }

        return $this;
    }

    public function removeAttachment(PetitionAttachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getPetition() === $this) {
                $attachment->setPetition(null);
            }
        }

        return $this;
    }

}
