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
     * setHeader Write header to file
     * @param   string       $headerText      String with header to write
     * @return  void
     */
    public function setHeader(string $headerText): void;

    /**
     * write Write to file
     * @param   string       $text      String with text to write
     * @return  void
     */
    public function write(string $text): void;

    /**
     * close Close file after write
     * @return  void
     */
    public function close(): void;

}
