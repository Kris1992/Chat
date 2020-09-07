<?php declare(strict_types=1);

namespace App\Services\FileWriter;

class TextFileWriter implements FileWriterInterface
{

    /** @var resource */
    private $file;

    public function open(string $absoluteFilePath): void
    {
        $this->file = fopen($absoluteFilePath, "w");
        if (!$this->file) {
            throw new \Exception('File open failed.');
        }  
    }

    public function write(string $text, string $headerText = null): void
    {   

        if ($headerText) {
            $completeText = $headerText . PHP_EOL;
            $completeText .= $text;
            fwrite($this->file, $completeText);
        } else {
            fwrite($this->file, $text);
        }
        
    }

    public function close(): void
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
}
