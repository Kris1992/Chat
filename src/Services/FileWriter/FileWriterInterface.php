<?php declare(strict_types=1);

namespace App\Services\FileWriter;

/**
 *  Write file
 */
interface FileWriterInterface
{   

    /**
     * open Open file to write (if file doesn't exist create new one)
     * @param   string       $absoluteFilePath      String with absolute path to file
     * @throws  Exception                           Throw exception when open/create file fails
     * @return  void
     */
    public function open(string $absoluteFilePath): void;

    /**
     * write Write to file
     * @param   string       $text              String with text to write
     * @param   string       $headerText        String with header to write [optional]
     * @return  void
     */
    public function write(string $text, string $headerText = null): void;

    /**
     * close Close file after write
     * @return  void
     */
    public function close(): void;

}
