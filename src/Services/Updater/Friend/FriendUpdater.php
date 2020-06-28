<?php
declare(strict_types=1);

namespace App\Services\Updater\Friend;

use App\Entity\Friend;

class FriendUpdater implements FriendUpdaterInterface 
{
    const STATUS_ACCEPT = 'accept';
    const STATUS_REJECT = 'reject';

    static private $statuses = [
        self::STATUS_ACCEPT => 'Accepted',
        self::STATUS_REJECT => 'Rejected',
    ];

    public function update(Friend $friend, string $status): Friend
    {
        if (isset(self::$statuses[$status])) {
            $statusType = self::$statuses[$status];
            $friend
                ->setStatus($statusType)
            ;
        }
        
        return $friend;
    }
}
