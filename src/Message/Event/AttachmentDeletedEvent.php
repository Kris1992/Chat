<?php declare(strict_types=1);

namespace App\Message\Event;

class AttachmentDeletedEvent
{

    /** @var string */
    private $subdirectory;

    /** @var string */
    private $filename;

    /** @var string */
    private $fileType;

    /** @var string */
    private $attachmentType;

    /**
     * AttachmentDeletedEvent Constructor
     * 
     * @param string $subdirectory
     * @param string $filename
     * @param string $fileType
     * @param string $attachmentType 
     */
    public function __construct(string $subdirectory, string $filename, string $fileType, string $attachmentType)
    {
        $this->subdirectory = $subdirectory;
        $this->filename = $filename;
        $this->fileType = $fileType;
        $this->attachmentType = $attachmentType;
    }

    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getAttachmentType(): string
    {
        return $this->attachmentType;
    }
    
}
