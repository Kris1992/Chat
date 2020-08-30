<?php declare(strict_types=1);

namespace App\Entity;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use Hateoas\Configuration\Annotation as Hateoas;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\BaseAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BaseAttachmentRepository::class)
 * @Hateoas\Relation("imagePath", 
 *     href = "expr(object.getImagePath())",
 *     exclusion = @Hateoas\Exclusion(groups={"attachment:show"})
 * ) 
 * @Hateoas\Relation("thumbImagePath", 
 *     href = "expr(object.getThumbImagePath())",
 *     exclusion = @Hateoas\Exclusion(groups={"attachment:show"})
 * ) 
 * @Hateoas\Relation("filePath", 
 *     href = "expr(object.getFilePath())",
 *     exclusion = @Hateoas\Exclusion(groups={"attachment:show"})
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"baseAttachment" = "BaseAttachment", "attachment" = "Attachment"})
 */
class BaseAttachment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $filename;

    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $type;

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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

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

    public function getImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES.'/'.$this->user->getLogin().'/'.$this->getFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::ATTACHMENTS_IMAGES.'/'.$this->user->getLogin().'/'.ImagesConstants::THUMB_IMAGES.'/'.$this->getFilename();
    }

    public function getFilePath(): ?string
    {
        return AttachmentsConstants::ATTACHMENTS_FILES.'/'.$this->user->getLogin().'/'.$this->getFilename();
    }

}
