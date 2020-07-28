<?php declare(strict_types=1);

namespace App\Entity;

use App\Services\AttachmentFileUploader\AttachmentsConstants;
use Hateoas\Configuration\Annotation as Hateoas;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\AttachmentRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;


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
 * 
 */
class Attachment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Message::class, inversedBy="attachments")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $type;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
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

    public function getImagePath(): ?string
    {
        return ImagesConstants::CHATS_IMAGES.'/'.$this->user->getLogin().'/'.$this->getFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::CHATS_IMAGES.'/'.$this->user->getLogin().'/'.ImagesConstants::THUMB_IMAGES.'/'.$this->getFilename();
    }

    public function getFilePath(): ?string
    {
        return AttachmentsConstants::CHATS_FILES.'/'.$this->user->getLogin().'/'.$this->getFilename();
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
