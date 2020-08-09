<?php declare(strict_types=1);

namespace App\Model\Report;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class ReportModel
{
    const VALID_TYPES = ['Offensive', 'Prohibited', 'Spam', 'Other'];

    private $id;

    /**
     * @Assert\Choice(choices=ReportModel::VALID_TYPES, message="Choose a valid type")
     */
    private $type;

	/**
     * @Assert\NotBlank(message="Please enter a description")
     * @Assert\Length(
     *      max = 400,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false,
     * )
     */
    private $description;

    /**
     * @Assert\NotNull(message="Report sender missing")
     */
    private $reportSender;

    /**
     * @Assert\NotNull(message="Reported user missing")
     */
    private $reportedUser;

    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getReportSender(): ?User
    {
        return $this->reportSender;
    }

    public function setReportSender(?User $reportSender): self
    {
        $this->reportSender = $reportSender;

        return $this;
    }

    public function getReportedUser(): ?User
    {
        return $this->reportedUser;
    }

    public function setReportedUser(?User $reportedUser): self
    {
        $this->reportedUser = $reportedUser;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

}
