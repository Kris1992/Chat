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
            throw new Exception('File open failed.');
        }  
    }

    public function setHeader(string $headerText): void
    {
        fwrite($this->file, $headerText . PHP_EOL);
    }

    public function write(string $text): void
    {
        fwrite($this->file, $text);
    }

    public function close(): void
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
}
