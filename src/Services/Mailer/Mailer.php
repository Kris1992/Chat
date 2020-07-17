<?php declare(strict_types=1);

namespace App\Services\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Entity\User;

/**
 * Service responsible for sending emails
 */
class Mailer implements MailingSystemInterface
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendResetPasswordMessage(User $user): TemplatedEmail
    {
        $message = (new TemplatedEmail())
            ->from(new Address('krakowdev01@gmail.com', 'Chat'))
            ->to(new Address($user->getEmail(), $user->getLogin()))
            ->subject('Reset password!')
            ->htmlTemplate('emails/reset_password_email.inky.twig')
            ->context([
                'user' => $user,
            ]);
        $this->mailer->send($message);

        return $message;
    }

}
