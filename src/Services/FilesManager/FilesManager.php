<?php
declare(strict_types=1);

namespace App\Services\FilesManager;

use League\Flysystem\{FilesystemInterface, FileNotFoundException};
use Symfony\Component\HttpFoundation\File\{UploadedFile, File};
use Psr\Log\LoggerInterface;
use Gedmo\Sluggable\Util\Urlizer;

class FilesManager implements FilesManagerInterface 
{

    /** @var FilesystemInterface */
    private $publicFilesystem;

    /** @var LoggerInterface */
    private $logger;

    /**
     * FilesManager Constructor
     *
     *@param FilesystemInterface $publicUploadsFilesystem
     *@param LoggerInterface $logger
     *
     */
    public function __construct(FilesystemInterface $publicUploadsFilesystem, LoggerInterface $logger)  
    {
        $this->publicFilesystem = $publicUploadsFilesystem;
        $this->logger = $logger;
    }

    public function upload(File $file, string $folderName): string
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilenameExtFree = $this->clearFilename($originalFilename);

        $extension = $file->guessExtension();
        $newFilename = $newFilenameExtFree.'.'.$extension;

        $stream = fopen($file->getPathname(), 'r');
        $result = $this->publicFilesystem->writeStream(
            $folderName.'/'.$newFilename,
            $stream
        );

        if (is_resource($stream)) {
            fclose($stream);
        }
        
        if (!$result) {
            $message = sprintf('Could not write uploaded file "%s"', $newFilename); 
            $this->logger->alert($message);
            throw new \Exception($message);
        }

        return $newFilename;
    }

    /**
     * clearFilename Clear filename form dots and generate unique name 
     * @param  string $filename Name of uploaded file
     * @return string           Return sanitazed filename
     */
    public static function clearFilename(string $filename): string
    {
        $clearFilename = str_replace('.', '_', $filename);
        $clearFilename = Urlizer::urlize(pathinfo($clearFilename, PATHINFO_FILENAME)).'-'.uniqid();
        
        return $clearFilename;
    } 
}
