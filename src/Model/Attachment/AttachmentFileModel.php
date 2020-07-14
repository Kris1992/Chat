<?php declare(strict_types=1);

namespace App\Model\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentFileModel
{

    /**
     * @Assert\NotNull(groups={"attachment:image"})
     * @Assert\Image(
     *     mimeTypes={
     *         "image/jpeg",
     *         "image/png"
     *     },
     *     maxSize="3M",
     *     groups={"attachment:image"})
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getFile(): File
    {
        return $this->file;
    }

}
