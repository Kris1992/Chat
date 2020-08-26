<?php declare(strict_types=1);

namespace App\Services\ChatPrinterSystem;

use App\Entity\{User, Chat};

/**
 *  Take care about all process of print chat messages between dates
 */
interface ChatPrinterSystemInterface
{   
    /**
     * printToFile Take care about print part of chat messages
     * @param   Chat                    $chat               Chat object to print
     * @param   User                    $currentUser        User object whose print part of chat
     * @param   \DateTimeInterface      $startDate          Start date of messages
     * @param   \DateTimeInterface      $stopDate           Stop date of messages
     * @throws  \Exception                                  Throws \Exception when print chat messages fails
     * @return  string                                      String with link to file
     */
    public function printToFile(Chat $chat, User $currentUser, \DateTimeInterface $startAt, \DateTimeInterface $stopAt, string $fileFormat): string;

}
