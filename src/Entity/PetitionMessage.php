<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PetitionMessageRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetitionMessageRepository::class)
 */
class PetitionMessage extends Message
{

    /**
     * @ORM\ManyToOne(targetEntity=Petition::class, inversedBy="petitionMessages")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"petition:message"})
     */
    private $petition;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"petition:message"})
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

    public function setReaded(): self
    {
        $this->readedAt = new \DateTime();

        return $this;
    }

}
