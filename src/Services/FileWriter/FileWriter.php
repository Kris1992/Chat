<?php declare(strict_types=1);

namespace App\Services\FileWriter;

/**
 *  Manage ConcreteWriter
 */
class FileWriter
{   
    
    public static function chooseWriter($writerName) {
        switch($writerName) {
            case FileWriterConstants::TXT_WRITER:
                return new TextFileWriter();
            case FileWriterConstants::CSV_WRITER:
                return new CsvFileWriter();
            default:
                throw new \Exception("Unsupported type of file to create");
        }
    }
}
