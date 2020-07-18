<?php declare(strict_types=1);

namespace App\Services\Factory\MessageModel;

use App\Services\AttachmentHelper\AttachmentHelperInterface;
use App\Model\Message\MessageModel;
use App\Entity\{User,Chat};

class MessageModelFactory implements MessageModelFactoryInterface 
{

    /** @var AttachmentHelperInterface */
    private $attachmentHelper;

    public function __construct(AttachmentHelperInterface $attachmentHelper)
    {
        $this->attachmentHelper = $attachmentHelper;
    }

    public function createFromData(?string $content, ?User $owner, ?Chat $chat): MessageModel
    {
        
        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($owner)
            ->setChat($chat)
            ;

        $filenames = $this->attachmentHelper->getAttachmentsFilenames($content);
        
        if ($filenames) {
            $attachments = $this->attachmentHelper->getAttachments($filenames, $owner);
            
            foreach ($attachments as $attachment) {
                $messageModel->addAttachment($attachment);  
            }       
        }

        return $messageModel;
    }

}
