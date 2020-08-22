<?php declare(strict_types=1);

namespace App\Services\Factory\Chat;

use App\Services\ImagesManager\ImagesManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

class ChatFactory implements ChatFactoryInterface 
{

    /** @var ImagesManagerInterface */
    private $attachmentImagesManager;

    /**
     * ChatFactory Constructor
     * 
     * @param ImagesManagerInterface $attachmentImagesManager
     */
    public function __construct(ImagesManagerInterface $attachmentImagesManager)  
    {
        $this->attachmentImagesManager = $attachmentImagesManager;
    }

    public function create(ChatModel $chatModel, User $owner, ?File $uploadedImage): Chat
    {

        /* From admin area only public chat rooms */
        if ($chatModel->getIsPublic() === null) {
            $chatModel->setIsPublic(true);   
        }

        $chat = new Chat();
        $chat
            ->setTitle($chatModel->getTitle())
            ->setDescription($chatModel->getDescription())
            ->setIsPublic($chatModel->getIsPublic())
            ->setOwner($owner)
            ;

        $participants = $chatModel->getParticipants();

        if ($participants) {
            foreach ($participants as $participant) {
                $chat
                    ->addParticipant($participant)
                ;
            }
        }

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
