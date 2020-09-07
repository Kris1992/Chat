<?php declare(strict_types=1);

namespace App\Services\PasswordTokenGenerator;

use App\Entity\{User, PasswordToken};

/**
 *  Take care about generate user password token
 */
interface PasswordTokenGeneratorInterface
{   

    /**
     * generate Generate new password token
     * @param   User                $user        User object whose is owner of new token
     * @throws  \Exception                       Throws an \Exception when save token fails
     * @return  PasswordToken
     */
    public function generate(User $user): PasswordToken;
}
