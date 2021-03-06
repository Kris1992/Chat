<?php declare(strict_types=1);

namespace App\Services\Factory\ChatModel;

use App\Services\Factory\Participant\ParticipantFactoryInterface;
use App\Model\Chat\ChatModel;
use App\Entity\{Chat, User};

class ChatModelFactory implements ChatModelFactoryInterface 
{
    
    /**
     * @var ParticipantFactoryInterface
     */
    private $participantFactory;

    /**
     * ChatModelFactory Constructor
     * 
     * @param ParticipantFactoryInterface $participantFactory
     * 
     */
    public function __construct(ParticipantFactoryInterface $participantFactory)
    {
        $this->participantFactory = $participantFactory;
    }

    public function create(Chat $chat): ChatModel
    {

        $chatModel = new ChatModel();
        $chatModel
            ->setId($chat->getId())
            ->setTitle($chat->getTitle())
            ->setDescription($chat->getDescription())
            ->setIsPublic($chat->getIsPublic())
            ->setOwner($chat->getOwner())
            ->setImageFilename($chat->getImageFilename())
            ;

        return $chatModel;
    }

    public function createFromData(User $owner, bool $isPublic, ?array $users, ?string $title, ?string $description): ChatModel
    {
        $chatModel = new ChatModel();
        $chatModel
            ->setOwner($owner)
            ->setIsPublic($isPublic)
            ->setDescription($description)
            ;

        if (!$isPublic) {
            $chatModel
                ->setTitle('Conversation')
               ;

            foreach ($users as $user) {
                $participant = $this->participantFactory->create($user, null);
                $chatModel
                    ->addParticipant($participant)
                    ;
            }

            $participant = $this->participantFactory->create($owner, null);
            $chatModel
                ->addParticipant($participant)
            ;

        } else {
            $chatModel
                ->setTitle($title)
                ;
        }

        return $chatModel;
    }

}
