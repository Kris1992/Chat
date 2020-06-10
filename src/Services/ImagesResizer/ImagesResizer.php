<?php
declare(strict_types=1);

namespace App\Services\ImagesResizer;

use Symfony\Component\HttpFoundation\File\{UploadedFile, File};
use App\Services\ImagesManager\ImagesConstants;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;

class ImagesResizer implements ImagesResizerInterface
{

    /** @var FilesystemInterface */
    private $publicFilesystem;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $uploadsDirectory;

    /**
     * ImagesResizer Constructor
     * 
     *@param FilesystemInterface    $publicUploadsFilesystem
     *@param LoggerInterface        $logger
     *@param string                 $uploadsDirectory
     *
     */
    public function __construct(FilesystemInterface $publicUploadsFilesystem, LoggerInterface $logger, string $uploadsDirectory)  
    {
        $this->publicFilesystem = $publicUploadsFilesystem;
        $this->logger = $logger;
        $this->uploadsDirectory = $uploadsDirectory;
    }

    public function compressImage(string $source, string $extension, int $newWidth, ?int $newHeight): void
    {
        
        if(!$this->islibraryLoaded()) {
            $this->logger->alert('Could not load GD library');
            throw new \Exception('Server library not found');
        }

        $pathInfo = pathinfo($source);
        $filePath = $pathInfo['dirname'];
        $basenameArray = explode('.', $pathInfo['basename']);
        $filenameExtFree = $basenameArray[0];
        $destinationFolder = $filePath.'/'.ImagesConstants::THUMB_IMAGES;

        $this->publicFilesystem->createDir($destinationFolder);

        $destination = $this->uploadsDirectory.'/'.$destinationFolder.'/'.$filenameExtFree.'.'.$extension;
        $absoluteSource = $this->uploadsDirectory.'/'.$source;

        try{
            //used only GD library
            switch ($extension) {
                case 'jpeg':
                    $image = imagecreatefromjpeg($absoluteSource);
                    $newImage = $this->resizeImage($absoluteSource, $image, $newWidth, $newHeight);
                    imagejpeg($newImage, $destination, ImagesConstants::JPEG_QUALITY);
                    $this->flushMemory($image, $newImage);
                    break;
                case 'gif':
                    $gifDecoder = new GIFDecoder(fread(fopen($absoluteSource, "rb"), filesize($absoluteSource)));
                    $delays = $gifDecoder->GIFGetDelays();
                    $tempPath = $filePath.'/'.ImagesConstants::TEMP_GIF.'/';
                    $this->publicFilesystem->createDir($tempPath);
            
                    $iterator = 1;

                    foreach ($gifDecoder->GIFGetFrames() as $frame) {
                        $tempImagePath = $tempPath.'image'.$iterator.'.gif';
                        fwrite(fopen($tempImagePath, 'wb'), $frame);
                        $this->resizeFrame($tempImagePath, $newWidth);
                        $iterator++;
                    }

                    $iterator = 1;

                    if ($tempDir = opendir($tempPath)){
                        while (false !== ($data = readdir($tempDir))){
                            if ($data != "." && $data != ".." ) {
                                $framesTemp[] = $tempPath.'image'.$iterator.'.gif';
                                $iterator++;
                            }
                        }
                        closedir($tempDir);
                    }

                    $gifEncoder = new GIFEncoder($framesTemp, $delays, 0, 2, 0, 0, 0, "url");
                    $fpThumb = fopen($destination, 'w');
                    fwrite($fpThumb, $gifEncoder->GetAnimation());
                    fclose($fpThumb);

                    $this->publicFilesystem->deleteDir($tempPath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($absoluteSource);
                    $newImage = $this->resizeImage($absoluteSource, $image, $newWidth, $newHeight);
                    imagepng($newImage, $destination, ImagesConstants::PNG_QUALITY);
                    $this->flushMemory($image, $newImage);
                    break;
                default:
                    throw new \Exception("Unsupported media type");
            }

        } catch(\Exception $e_img) {
            throw new \Exception("Error: ".$e_img);
        }    
   }

   /**
    * resizeFrame Resize frame of gif image
    * @param  string    $tempImagePath  Path to image in temp folder
    * @param  int       $newWidth       Target width
    * @throws Exception                 Throws an exception when resize fails
    * @return void
    */
   private function resizeFrame(string $tempImagePath, int $newWidth): void
   {

        list($originalWidth, $originalHeight, $imageInfo) = getimagesize($tempImagePath);

        switch ($imageInfo) {

            case 1: $img = imagecreatefromgif($tempImagePath); break;

            case 2: $img = imagecreatefromjpeg($tempImagePath);  break;

            case 3: $img = imagecreatefrompng($tempImagePath); break;

            default: throw new \Exception('Unsupported file extension');  break;
        }

        list($newWidth, $newHeight) = $this->getNewSize($originalWidth, $originalHeight, $newWidth);

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        //dump($imageInfo);
        if(($imageInfo === 1) || ($imageInfo === 3)) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage,true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($newImage, $img, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        switch ($imageInfo) {

            case 1: imagegif($newImage, $tempImagePath); break;

            case 2: imagejpeg($newImage, $tempImagePath); break;

            case 3: imagepng($newImage, $tempImagePath); break;

            default: throw new \Exception('Failed resizing image'); break;
        }

        $this->flushMemory($img, $newImage);
   }

   /**
    * flushMemory Frees any memory associated with images 
    * @param  resource $img      First resource
    * @param  resource $newImage Second resource
    * @return void
    */
   private function flushMemory($img, $newImage): void
   {
        imagedestroy($newImage);
        imagedestroy($img);
   }
   
   /**
    * resizeImage Resize image
    * @param  string    $absoluteSource     Absolute path to source with filename
    * @param  resource  $image              Image resource
    * @param  int       $newWidth           Width of new image
    * @param  int|null  $newHeight          Height of new image (if given ratio of image will be changed) [optional]
    * @throws Exception                     Throws an exception when resize fails
    * @return resource
    */
   private function resizeImage(string $absoluteSource, $image, int $newWidth, ?int $newHeight)
   {

        list($originalWidth, $originalHeight) = getimagesize($absoluteSource);
        
        if (!$newHeight) {
            list($newWidth, $newHeight) = $this->getNewSize($originalWidth, $originalHeight, $newWidth);
        }

        //resize_image() 
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($newImage, false);
        $result = imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        imagesavealpha($newImage, true);

        if (!$result) {
            throw new \Exception(sprintf('Could not compress uploaded Image "%s"', $filename));
        }
    
        return $newImage;
   }

   /**
    * getNewSize Establish new size of image (width and height) and return it
    * @param  int    $originalWidth     Original width
    * @param  int    $originalHeight    Original height
    * @param  int    $newWidth          Destination width
    * @return array                     Array with new size
    */
   private function getNewSize(int $originalWidth, int $originalHeight, int $newWidth): array
   {     
        if($originalWidth > $newWidth) {
            $newHeight = ($originalHeight * $newWidth)/$originalWidth;
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        return array(intval($newWidth), intval($newHeight));
   }

    /**
     * islibraryLoaded  Check library gd or gd2 can be used
     * @return bool
     */
    private function islibraryLoaded(): bool
    {
        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            return false;
        }

        return true;
    }

}