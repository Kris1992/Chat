<?php
declare(strict_types=1);

namespace App\Model\Chat;

use Symfony\Component\Validator\Constraints as Assert;

class ChatModel
{
    
    private $id;

	/**
     * @Assert\NotBlank(message="Please enter a title")
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false
     * )
     */
    private $title;

    private $description;

    /* if is private 2 users needed*/
    private $users;

    private $isPublic;

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

}
