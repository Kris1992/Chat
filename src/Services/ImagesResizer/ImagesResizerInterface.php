<?php declare(strict_types=1);

namespace App\Services\ImagesResizer;

/**
 *  Resize and compress images
 */
interface ImagesResizerInterface
{   

    /**
     * compressImage Compress image to smaller one thumb image
     * @param  string       $source     Relative path from uploads dir to source file 
     * @param  string       $extension  Extension of file
     * @param  integer      $newWidth   Width of compressed image
     * @param  int|null     $newHeight  Height of new image (if given ratio of image will be changed) [optional]
     * @throws Exception                Throws an exception when compress process fails
     * @return void
     */
    public function compressImage(string $source, string $extension, int $newWidth, ?int $newHeight): void;

}
