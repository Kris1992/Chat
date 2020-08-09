<?php declare(strict_types=1);

namespace App\Services\FileWriter;

class CsvFileWriter implements FileWriterInterface
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

    public function write(string $text, string $headerText = null): void
    {   
        if($headerText) {
            $headers = explode(FileWriterConstants::CSV_DELIMITER, $headerText);
            fputcsv($this->file, $headers);
        }

        $linesArray = explode("\n", $text);
        foreach ($linesArray as $line) {
            $fields = explode(FileWriterConstants::CSV_DELIMITER, $line);
            fputcsv($this->file, $fields);
        }
    }

    public function close(): void
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
}
