<?php declare(strict_types=1);

namespace App\Services\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;

/**
 * Manage sending emails
 */
interface MailingSystemInterface
{   

    /**
     * sendResetPasswordMessage Sending email with reset password message
     * @param  User   $user User whose want reset password
     * @return TemplatedEmail
     */
    public function sendResetPasswordMessage(User $user): TemplatedEmail;

}
