<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\ParticipantRepository;
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

}
