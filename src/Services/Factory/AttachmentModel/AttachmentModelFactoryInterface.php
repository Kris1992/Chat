<?php declare(strict_types=1);

namespace App\Services\Factory\AttachmentModel;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\Attachment\AttachmentModel;
use App\Entity\{Message, User};

/**
 *  Take care about creating message attachment model
 */
interface AttachmentModelFactoryInterface
{   
    /**
     * createFromData Create message attachment model from data
     * @param   User            $user           User object whose is owner of message
     * @param   Message         $message        Message object which is owner of attachment
     * @param   File            $file           File object
     * @param   string          $type           String with type of attachment e.g image
     * @return  AttachmentModel                 Return attachment model object
     * @throws  \Exception                      Throws \Exception when upload file fails 
     */
    public function createFromData(User $user, ?Message $message, File $file, string $type): AttachmentModel;

}
