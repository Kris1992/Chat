<?php declare(strict_types=1);

namespace App\Services\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Entity\{User, Petition};

/**
 * Service responsible for sending emails
 */
class Mailer implements MailingSystemInterface
{
    /** @var MailerInterface */
    private $mailer;

    /**
     * MailerInterface Constructor
     * 
     * @param MailerInterface $mailer
     * 
     */
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

    public function sendAnsweredPetitionMessage(Petition $petition): TemplatedEmail
    {
        $message = (new TemplatedEmail())
            ->from(new Address('krakowdev01@gmail.com', 'Chat'))
            ->to(new Address(
                $petition->getPetitioner()->getEmail(), 
                $petition->getPetitioner()->getLogin())
            )
            ->subject('Your petition has new answer!')
            ->htmlTemplate('emails/answered_petition.inky.twig')
            ->context([
                'petition' => $petition,
            ]);
        $this->mailer->send($message);

        return $message;
    }

}
