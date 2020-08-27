<?php declare(strict_types=1);

namespace App\Services\BanManager;

use App\Entity\User;

/** 
 *  Interface for block or unblock user account
 */
interface BanManagerInterface
{
    /**
     * ban Ban user
     * @param  User   $user   User object which will be banned
     * @param  int    $option Integer with ban time option
     * @throws \Exception     Throw an exception when something goes wrong
     * @return User            
     */
    public function ban(User $user, int $option): User;

    /**
     * unBan Take off ban
     * @param  User   $user   User object which will be unbanned
     * @throws \Exception     Throw an exception when something goes wrong
     * @return User           
     */
    public function unBan(User $user): User;
}
