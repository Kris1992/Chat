<?php declare(strict_types=1);

namespace App\Message\Command;

class CheckIsAttachmentUsed
{

    /** @var int */
    private $id;

    /** @var string */
    private $subdirectory;

    /**
     * CheckIsAttachmentUsed Constructor 
     * @param int    $id           Int with attachment id
     * @param string $subdirectory String with subdirectory
     */
    public function __construct(int $id, string $subdirectory)
    {
        $this->id = $id;
        $this->subdirectory = $subdirectory;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

}
