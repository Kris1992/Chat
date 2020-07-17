<?php declare(strict_types=1);

namespace App\Services\AttachmentsHelper;

use App\Repository\AttachmentRepository;
use App\Entity\User;

class AttachmentsHelper implements AttachmentsHelperInterface 
{

    /** @var AttachmentRepository */
    private $attachmentRepository;

    public function __construct(AttachmentRepository $attachmentRepository)
    {
        $this->attachmentRepository = $attachmentRepository;
    }

    public function getAttachmentsFilenames(string $content): ?array
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
