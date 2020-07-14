<?php declare(strict_types=1);

namespace App\Model\Chat;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Entity\{User, Participant};

class ChatModel
{
    
    private $id;

	/**
     * @Assert\NotBlank(message="Please enter a title", groups={"chat:public", "chat:private"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false,
     *      groups={"chat:public", "chat:private"}
     * )
     */
    private $title;

    private $isPublic;

    private $owner;

    /* if is private 2 at least users needed*/
    /**
     * @Assert\Valid
     * @Assert\Count(
     *      min = 2,
     *      minMessage = "You must invite at least one person",
     *      groups={"chat:private"}
     * )
     */
    private $participants;
    
    /** 
     * @Assert\NotBlank(message="Please enter a description", groups={"chat:public"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false,
     *      groups={"chat:public"}
     * )
     */
    private $description;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }
    
    public function setIsPublic(?bool $isPublic): self
    {
        $this->isPublic = $isPublic;

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

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

}
