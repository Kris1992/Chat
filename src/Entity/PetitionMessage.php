<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PetitionMessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetitionMessageRepository::class)
 */
class PetitionMessage extends Message
{

    /**
     * @ORM\ManyToOne(targetEntity=Petition::class, inversedBy="petitionMessages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $petition;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readedAt;

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
