<?php declare(strict_types=1);

namespace App\Message\Command;

class RemoveScreenFile
{

    /** @var string */
    private $filename;

    /**
     * RemoveScreenFile Constructor 
     * @param string    $filename           String with name of file to remove
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
    
}
