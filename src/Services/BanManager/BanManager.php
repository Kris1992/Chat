<?php declare(strict_types=1);

namespace App\Services\BanManager;

use App\Entity\User;

class BanManager implements BanManagerInterface 
{

    /** @var array Array with possible ban times */
    private $fields = ['+1 day', '+7 days', '+1 month', '+3 months'];

    public function ban(User $user, int $option): User
    {
        if ($user->isAdmin() || $user->isBanned() || !isset($this->fields[$option])) {
            throw new \Exception("Cannot ban this user.");
        }

        $user->setBanTo(new \DateTime('now '.$this->fields[$option]));

        return $user;

    }

    public function unBan(User $user): User
    {
        if (!$user->isBanned()) {
            throw new \Exception("Cannot unban this user.");
        }

        $user->setBanTo(null);

        return $user;
    }

}
