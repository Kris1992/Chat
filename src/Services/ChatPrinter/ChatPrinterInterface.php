<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use Doctrine\Common\Collections\Collection;
use App\Entity\User;

/**
 *  Manage printing chat messages to files
 */
interface ChatPrinterInterface
{   

    /**
     * printToFile  Print messages to file
     * @param   Collection              $messages       Collection with messages
     * @param   User                    $currentUser    Current user object
     * @param   \DateTimeInterface      $startDate      Start date of messages to get
     * @param   \DateTimeInterface      $stopDate       Stop date of messages to get
     * @return  string                                  String with link to file 
     */
    public function printToFile(Collection $messages, User $currentUser, \DateTimeInterface $startDate, \DateTimeInterface $stopDate): string;

}