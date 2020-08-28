<?php declare(strict_types=1);

namespace App\Services\Factory\MessageModel;

use App\Services\AttachmentHelper\AttachmentHelperInterface;
use App\Model\Message\MessageModel;
use App\Entity\{User,Chat, Petition};

class MessageModelFactory implements MessageModelFactoryInterface 
{

    /** @var AttachmentHelperInterface */
    private $attachmentHelper;

    /**
     * MessageModelFactory Constructor
     * 
     * @param AttachmentHelperInterface $attachmentHelper
     */
    public function __construct(AttachmentHelperInterface $attachmentHelper)
    {
        $this->attachmentHelper = $attachmentHelper;
    }

    public function createFromData(?string $content, ?User $owner, ?Chat $chat, ?Petition $petition): MessageModel
    {
        
        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($owner)
            ->setChat($chat)
            ->setPetition($petition)
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
