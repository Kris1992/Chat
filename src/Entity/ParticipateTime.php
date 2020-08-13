<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParticipateTimeRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipateTimeRepository::class)
 */
class ParticipateTime
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="participateTimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participant;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $stopAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function getStopAt(): ?\DateTimeInterface
    {
        return $this->stopAt;
    }

    public function setStopAt(?\DateTimeInterface $stopAt): self
    {
        $this->stopAt = $stopAt;

        return $this;
    }
}
