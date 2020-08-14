<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\ParticipantRepository;
use App\Repository\ParticipateTimeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 */
class Participant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"chat:participants"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"chat:participants"})
     * @MaxDepth(2)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Chat::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $chat;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastSeenAt;

    /**
     * @ORM\OneToMany(targetEntity=ParticipateTime::class, mappedBy="participant", orphanRemoval=true, cascade={"persist"})
     */
    private $participateTimes;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"chat:participants"})
     */
    private $isRemoved;

    public function __construct()
    {
        $this->participateTimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeInterface $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function updateLastSeenAt(): self
    {
        $this->lastSeenAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection|ParticipateTime[]
     */
    public function getParticipateTimes(): Collection
    {
        return $this->participateTimes;
    }

    /**
     * getParticipateTimesBeforeDate Get All participateTimes before given date
     * @param \DateTimeInterface                $date
     * @return Collection|ParticipateTime[]
     */
    public function getParticipateTimesBeforeDate(\DateTimeInterface $date): Collection
    {
        $criteria = ParticipateTimeRepository::createBeforeDateCriteria($date);

        return $this->participateTimes->matching($criteria);
    }

    public function addParticipateTime(ParticipateTime $participateTime): self
    {
        if (!$this->participateTimes->contains($participateTime)) {
            $this->participateTimes[] = $participateTime;
            $participateTime->setParticipant($this);
        }

        return $this;
    }

    public function removeParticipateTime(ParticipateTime $participateTime): self
    {
        if ($this->participateTimes->contains($participateTime)) {
            $this->participateTimes->removeElement($participateTime);
            // set the owning side to null (unless already changed)
            if ($participateTime->getParticipant() === $this) {
                $participateTime->setParticipant(null);
            }
        }

        return $this;
    }

    public function getIsRemoved(): ?bool
    {
        return $this->isRemoved;
    }

    public function setIsRemoved(bool $isRemoved): self
    {
        $this->isRemoved = $isRemoved;

        return $this;
    }

}
