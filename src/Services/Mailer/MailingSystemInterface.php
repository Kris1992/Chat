<?php declare(strict_types=1);

namespace App\Services\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\{User, Petition};

/**
 * Manage sending emails
 */
interface MailingSystemInterface
{   

    /**
     * sendResetPasswordMessage Sending email with reset password message
     * @param  User             $user User whose want reset password
     * @return TemplatedEmail
     */
    public function sendResetPasswordMessage(User $user): TemplatedEmail;

    /**
     * sendAnsweredPetitionMessage Sending email with information about answered petition
     * @param  Petition         $petition       Petition which get answer
     * @return TemplatedEmail
     */
    public function sendAnsweredPetitionMessage(Petition $petition): TemplatedEmail;

}
