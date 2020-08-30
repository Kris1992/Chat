<?php declare(strict_types=1);

namespace App\Services\AttachmentHelper;

use App\Repository\AttachmentRepository;
use App\Entity\User;

class AttachmentHelper implements AttachmentHelperInterface 
{

    /** @var AttachmentRepository */
    private $attachmentRepository;

    /**
     * AttachmentHelper Constructor
     *
     *@param AttachmentRepository $attachmentRepository
     *
     */
    public function __construct(AttachmentRepository $attachmentRepository)
    {
        $this->attachmentRepository = $attachmentRepository;
    }

    public function getAttachmentsFilenames(?string $content): ?array
    {
        if ($content) {
            $pattern = '~< *img[^>]*src *= *["\']?([^"\']*)|<a class="uploaded-file"[^>]*href *= *["\']?([^"\']*)~';

            preg_match_all($pattern, $content, $matches);
            if ($matches[0]) {
                $filenames = array_map(function($imageMatch, $fileMatch) {
                    if ($imageMatch) {
                        return basename($imageMatch);
                    } else if ($fileMatch) {
                        return basename($fileMatch);
                    } 
                    return;
                }, $matches[1], $matches[2]);

                return $filenames;
            }
        }
       
        return null;
    }
    
    public function getAttachments(array $filenames, User $user): array
    {
        $attachments = [];

        foreach ($filenames as $filename) {
            $attachment = $this->attachmentRepository->findOneBy([
                'filename' => $filename,
                'user' => $user
            ]);
            if ($attachment) {
                array_push($attachments, $attachment);
            }
        }

        return $attachments;
    }
}
