<?php declare(strict_types=1);

namespace App\Services\Factory\MessageModel;

use App\Services\AttachmentsHelper\AttachmentsHelperInterface;
use App\Model\Message\MessageModel;
use App\Entity\{User,Chat};

class MessageModelFactory implements MessageModelFactoryInterface 
{

    /** @var AttachmentsHelperInterface */
    private $attachmentsHelper;

    public function __construct(AttachmentsHelperInterface $attachmentsHelper)
    {
        $this->attachmentsHelper = $attachmentsHelper;
    }

    public function createFromData(?string $content, ?User $owner, ?Chat $chat): MessageModel
    {
        
        $messageModel = new MessageModel();
        $messageModel
            ->setContent($content)
            ->setOwner($owner)
            ->setChat($chat)
            ;

        $attachments = $this->attachmentsHelper->getAttachments($content);
        dump($attachments);
        if ($attachments) {
            
        }

        return $messageModel;
    }

}
