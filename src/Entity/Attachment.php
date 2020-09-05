<?php declare(strict_types=1);

namespace App\Entity;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use Hateoas\Configuration\Annotation as Hateoas;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\AttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AttachmentRepository::class)
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
 * @ORM\DiscriminatorMap({"attachment" = "Attachment", "messageAttachment" = "MessageAttachment", "petitionAttachment" = "PetitionAttachment"})
 */
class Attachment
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
     * @Groups({"attachment:show"})
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

}
