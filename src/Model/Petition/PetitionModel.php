<?php declare(strict_types=1);

namespace App\Model\Petition;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\{ArrayCollection, Collection};

class PetitionModel
{
    
    private $id;

    /**
     * @Assert\NotBlank(message="Please enter a title")
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false,
     * )
     */
    private $title;

    /**
     * @Assert\Choice(choices=PetitionConstants::VALID_TYPES, message="Choose a valid type")
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Please enter a description")
     */
    private $description;

    private $petitioner;

    private $attachementsFilenames;

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

    public function getAttachementsFilenames(): ?array
    {
        return $this->attachementsFilenames;
    }

    public function setAttachementsFilenames(?array $attachementsFilenames): self
    {
        $this->attachementsFilenames = $attachementsFilenames;

        return $this;
    }

}
