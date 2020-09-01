<?php declare(strict_types=1);

namespace App\Message\Command;

class CheckIsAttachmentUsed
{

    /** @var int */
    private $id;

    /** @var string */
    private $subdirectory;

    /** @var string */
    private $attachmentType;

    /**
     * CheckIsAttachmentUsed Constructor 
     * @param int    $id                Int with attachment id
     * @param string $subdirectory      String with subdirectory
     * @param string $attachmentType    String with type of attachment e.g Chat, Petition
     */
    public function __construct(int $id, string $subdirectory, string $attachmentType)
    {
        $this->id = $id;
        $this->subdirectory = $subdirectory;
        $this->attachmentType = $attachmentType;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    public function getAttachmentType(): string
    {
        return $this->attachmentType;
    }

}
