<?php
declare(strict_types=1);

namespace App\Services\ImagesManager;

/**
 * Handler of all constants with directories for ImagesManagers and Qualities for ImageResizer
 */
class ImagesConstants
{
    const USERS_IMAGES = 'users_images';
    const THUMB_IMAGES = 'thumb';
    const TEMP_GIF = 'gif_temp';
    const JPEG_QUALITY = 75;
    const PNG_QUALITY = 0;

}
