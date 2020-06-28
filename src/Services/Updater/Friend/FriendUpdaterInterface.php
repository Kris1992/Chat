<?php 
declare(strict_types=1);

namespace App\Services\Updater\Friend;

use App\Entity\Friend;

/** 
 *  Interface for updating Friend entities
 */
interface FriendUpdaterInterface
{
    /**
     * update Update entity class
     * @param   Friend  $friend     Friend object whose will be updated
     * @param   string  $status     String with new status
     * @return  Friend
     */
    public function update(Friend $friend, string $status): Friend;
}
