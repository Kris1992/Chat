<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\ChatMessageRepository;
use App\Services\ImagesManager\ImagesConstants;

/**
 * @ORM\Entity(repositoryClass=ChatRepository::class)
 */
class Chat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"chat:message", "chat:list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToOne(targetEntity=ChatMessage::class, cascade={"persist", "remove"})
     * @Groups({"chat:list"})
     */
    private $lastMessage;

    /**
     * @ORM\OneToMany(targetEntity=ChatMessage::class, mappedBy="chat", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Participant::class, mappedBy="chat", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups({"chat:participants"})
     * @MaxDepth(3)
     */
    private $participants;

    /**
     * @Gedmo\Timestampable(on="create")
     * @Gedmo\Timestampable(on="change", field={"messages", "title"})
     * @ORM\Column(type="datetime")
     */
    private $lastActivityAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
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

    public function setIsPublic(bool $isPublic): self
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

    public function getLastMessage(): ?ChatMessage
    {
        return $this->lastMessage;
    }

    public function setLastMessage(?ChatMessage $lastMessage): self
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ChatMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(ChatMessage $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    /**
     * getMessagesBetween   Get chat messages between start and stop date 
     * @param \DateTimeInterface    $startDate      Start date of messages to get
     * @param \DateTimeInterface    $stopDate       Stop date of messages to get
     * @return Collection|Message[]
     */
    public function getMessagesBetween(\DateTimeInterface $startDate, \DateTimeInterface $stopDate): Collection
    {
        $criteria = ChatMessageRepository::createBetweenDatesCriteria($startDate, $stopDate);

        return $this->messages->matching($criteria);
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
            $participant->setChat($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            // set the owning side to null (unless already changed)
            if ($participant->getChat() === $this) {
                $participant->setChat(null);
            }
        }

        return $this;
    }

    /**
     * getOtherParticipants Get chat participants without given user 
     * @param   User      $user       User object which should be not included to participants list
     * @return  Collection|Participant[]
     */
    public function getOtherParticipants(User $user): Collection
    {
        $criteria = ParticipantRepository::createNotIncludedUserCriteria($user);

        return $this->participants->matching($criteria);
    }

    /**
     * isCurrentParticipantRemoved      Check is participant with given user removed
     * @param   User      $user         User object which should be participant
     * @return  ?bool                   Return bool or null if user is not a participant of chat
     */
    public function isCurrentParticipantRemoved(User $user): ?bool
    {
        foreach ($this->participants as $participant) {
            if ($participant->getUser() ===  $user) {
                return $participant->getIsRemoved();
            }
        }

        return null;
    }

    /**
     * hasParticipant Check chat has participant with given user
     * @param   User      $user       User object which should be participant
     * @return  bool
     */
    public function hasParticipant(User $user): bool
    {
        foreach ($this->participants as $participant) {
            if ($participant->getUser() ===  $user) {
                return true;
            }
        }

        return false;
    }

    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return ImagesConstants::CHATS_IMAGES.'/'.$this->getOwner()->getLogin().'/'.$this->getImageFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::CHATS_IMAGES.'/'.$this->getOwner()->getLogin().'/'.ImagesConstants::THUMB_IMAGES.'/'.$this->getImageFilename();
    }

}
