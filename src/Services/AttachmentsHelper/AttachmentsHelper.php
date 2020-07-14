<?php declare(strict_types=1);

namespace App\Services\AttachmentsHelper;

class AttachmentsHelper implements AttachmentsHelperInterface 
{

    public function getAttachments(string $content): ?array
    {

        $pattern = '~< *img[^>]*src *= *["\']?([^"\']*)~';
        preg_match_all($pattern, $content, $matches);
        if ($matches) {
            $filenames = array_map(function($match) {
                return basename($match);
            }, $matches[1]);
            return $filenames;
        }
       
        return null;
    }
  
}
