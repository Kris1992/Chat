<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChatMessageRepository::class)
 */
class ChatMessage extends Message
{
    /**
     * @ORM\ManyToOne(targetEntity=Chat::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"chat:message"})
     */
    private $chat;

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

}
