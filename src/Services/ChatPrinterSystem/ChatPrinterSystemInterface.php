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
     * @param   Chat                    $chat               Chat object to print messages from
     * @param   User                    $currentUser        User object whose print part of chat
     * @param   \DateTimeInterface      $startDate          Start date of messages
     * @param   \DateTimeInterface      $stopDate           Stop date of messages
     * @param   string                  $fileFormat         String with format of file to print into
     * @throws  \Exception                                  Throws an \Exception when print chat messages fails
     * @return  string                                      String with link to file
     */
    public function printToFile(Chat $chat, User $currentUser, \DateTimeInterface $startAt, \DateTimeInterface $stopAt, string $fileFormat): string;

}
