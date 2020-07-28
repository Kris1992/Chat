<?php declare(strict_types=1);

namespace App\Model\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentFileModel
{

    /**
     * @Assert\NotNull(groups={"attachment:image", "attachment:file"})
     * @Assert\Image(
     *     mimeTypes={
     *         "image/jpeg",
     *         "image/png"
     *     },
     *     maxSize="3M",
     *     groups={"attachment:image"})
     * @Assert\File(
     *     maxSize = "3M",
     *     mimeTypes = {
     *         "application/pdf", 
     *         "application/x-pdf", 
     *         "application/msword", 
     *         "text/*", 
     *         "audio/*", 
     *         "video/*"
     *     },
     *     groups={"attachment:file"})
     * )
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
