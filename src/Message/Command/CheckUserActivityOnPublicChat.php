<?php declare(strict_types=1);

namespace App\Message\Command;

class CheckUserActivityOnPublicChat
{

    /** @var int */
    private $participantId;

    public function __construct(int $participantId)
    {
        $this->participantId = $participantId;
    }

    public function getParticipantId(): int
    {
        return $this->participantId;
    }

}
