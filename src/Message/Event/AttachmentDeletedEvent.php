<?php declare(strict_types=1);

namespace App\Message\Event;

class AttachmentDeletedEvent
{

    /** @var string */
    private $subdirectory;

    /** @var string */
    private $filename;

    /** @var string */
    private $type;

    public function __construct(string $subdirectory, string $filename, string $type)
    {
        $this->subdirectory = $subdirectory;
        $this->filename = $filename;
        $this->type = $type;
    }

    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getType(): string
    {
        return $this->type;
    }
    
}
