<?php declare(strict_types=1);

namespace App\Services\Factory\Chat;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

/**
 *  Take care about creating chat rooms
 */
interface ChatFactoryInterface
{   

    /**
     * create Create chat rooms
     * @param   ChatModel       $chatModel          Model with chat room data
     * @param   User            $owner              User object whose is the owner of chat
     * @param   File|null       $uploadedImage      Uploaded image [optional]
     * @return  Chat                                Return chat object
     */
    public function create(ChatModel $chatModel, User $owner, ?File $uploadedImage): Chat;

}
