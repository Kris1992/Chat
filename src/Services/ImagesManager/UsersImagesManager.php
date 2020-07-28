<?php declare(strict_types=1);

namespace App\Services\ImagesManager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Asset\Context\RequestStackContext;
use App\Services\ImagesResizer\ImagesResizerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Psr\Log\LoggerInterface;

class UsersImagesManager implements ImagesManagerInterface
{

    /** @var FilesManagerInterface */
    private $filesManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var RequestStackContext */
    private $requestStackContext;

    /** @var ImagesResizerInterface */
    private $imagesResizer;

    /** @var string */
    private $publicAssetBaseUrl;
    

    /**
     * UsersImagesManager Constructor
     *
     *@param FilesManagerInterface  $filesManager
     *@param LoggerInterface        $logger
     *@param RequestStackContext    $requestStackContext
     *@param ImagesResizerInterface $imagesResizer
     *@param string                 $uploadedAssetsBaseUrl
     *
     */
    public function __construct(FilesManagerInterface $filesManager, LoggerInterface $logger,  RequestStackContext $requestStackContext, ImagesResizerInterface $imagesResizer, string $uploadedAssetsBaseUrl)
    {
        $this->filesManager = $filesManager;
        $this->logger = $logger;
        $this->requestStackContext = $requestStackContext;
        $this->imagesResizer = $imagesResizer;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadImage(File $file, ?string $existingFilename, ?string $subdirectory, $newWidth = 100): ?string
    {
        
        if($subdirectory) {
            $directory =  ImagesConstants::USERS_IMAGES.'/'.$subdirectory;
            $newFilename = $this->uploadFile($file, $directory, $newWidth);

            if ($existingFilename && $newFilename) {
                $result = $this->deleteOldImage($existingFilename, $subdirectory);
                
                if(!$result) {
                    $this->logger->alert(sprintf('User upload new file but deleting old one fails. Check file: "%s" exist!!', $existingFilename));
                }
            }

        } else {
            $this->logger->alert('Users image uploader: Subdirectory missing, cannot upload image!!');
            $newFilename = null;
        }

        return $newFilename;
    }

    public function deleteImage(string $existingFilename, ?string $subdirectory): bool
    {
        if ($existingFilename && $subdirectory) {
           return $this->deleteOldImage($existingFilename, $subdirectory);
        }

        return false;
    }


    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext
            ->getBasePath().$this->publicAssetBaseUrl.'/'.$path;
    }
    
    /**
     * uploadFile Function which take care about upload image process
     * @param  File   $file         Uploaded file
     * @param  string $directory    Destination directory
     * @param  int    $newWidth     Width of compress image
     * @return string|null          Return filename or null if upload fails             
     */
    private function uploadFile(File $file, string $directory, int $newWidth): ?string
    {
        try {
            $newFilename = $this->filesManager->upload($file, $directory);    
        } catch (\Exception $e) {
            return null;
        }
        
        try {
            $path = $directory.'/'.$newFilename;
            $this->imagesResizer->compressImage($path, $file->guessExtension(), $newWidth, null);
        } catch (\Exception $e) {
            $subdirectory = str_replace(ImagesConstants::USERS_IMAGES.'/', '', $directory);
            /* Delete already uploaded big one image (user back to old one image) */
            $this->deleteOldImage($newFilename, $subdirectory);
            return null;    
        }        

        return $newFilename;
    }

    /**
     * deleteOldImage  Delete images (original and compressed) from server
     * @param  string $existingFilename     Filename of image
     * @param  string  $subdirectory        Subdirectory for image
     * @return bool                         Return true if success otherwise false
     */
    private function deleteOldImage(string $existingFilename, string $subdirectory): bool
    {

        $result = $this->filesManager->delete(ImagesConstants::USERS_IMAGES.'/'.$subdirectory.'/'.$existingFilename);
        $resultThumb = $this->filesManager->delete(ImagesConstants::USERS_IMAGES.'/'.$subdirectory.'/'.ImagesConstants::THUMB_IMAGES.'/'.$existingFilename);
        
        if (!$result || !$resultThumb) {
            return false;
        }

        return true;   
    }

}