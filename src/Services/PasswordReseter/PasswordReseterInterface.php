<?php declare(strict_types=1);

namespace App\Services\PasswordReseter;

/**
 *  Take care about reset user password
 */
interface PasswordReseterInterface
{   

    /**
     * reset Reset user password
     * @param   string                $email        String with user email
     * @param   string                $csrfToken    String with csrf token from post
     * @throws  \Exception                          Throws exception when csrfToken is not valid or user with given email not exist
     * @return  void
     */
    public function reset(string $email, string $csrfToken);
}
