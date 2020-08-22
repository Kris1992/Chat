<?php declare(strict_types=1);

namespace App\Services\Updater\Chat;

use App\Services\ImagesManager\ImagesManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Model\Chat\ChatModel;
use App\Entity\Chat;

class ChatUpdater implements ChatUpdaterInterface 
{   

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /**
     * ChatUpdater Constructor
     * 
     * @param ImagesManagerInterface $attachmentImagesManager
     */
    public function __construct(ImagesManagerInterface $attachmentImagesManager)  
    {
        $this->attachmentImagesManager = $attachmentImagesManager;
    }

    public function update(ChatModel $chatModel, Chat $chat, ?File $uploadedImage): Chat
    {

        $chat
            ->setTitle($chatModel->getTitle())
            ->setDescription($chatModel->getDescription())
            ;

        if ($uploadedImage) {
            $newFilename = $this->attachmentImagesManager->uploadImage(
                $uploadedImage, 
                $chat->getImageFilename(), 
                $chat->getOwner()->getLogin());
            
            if ($newFilename) {
                $chat->setImageFilename($newFilename);
            }
        }
        
        return $chat;
    }

}
