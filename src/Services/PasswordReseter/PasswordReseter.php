<?php declare(strict_types=1);

namespace App\Services\PasswordReseter;

use Symfony\Component\Security\Csrf\{CsrfTokenManagerInterface, CsrfToken};
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use App\Services\PasswordTokenGenerator\PasswordTokenGeneratorInterface;
use App\Services\Mailer\MailingSystemInterface;
use App\Repository\UserRepository;
use App\Entity\PasswordToken;

class PasswordReseter implements PasswordReseterInterface 
{

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var UserRepository */
    private $userRepository;

    /** @var PasswordTokenGeneratorInterface */
    private $passwordTokenGenerator;

    /** @var MailingSystemInterface */
    private $mailer;

    /**
     * PasswordReseter Constructor
     * 
     * @param CsrfTokenManagerInterface             $csrfTokenManager
     * @param UserRepository                        $userRepository
     * @param PasswordTokenGeneratorInterface       $passwordTokenGenerator
     * @param MailingSystemInterface                $mailer
     */
    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, UserRepository $userRepository, PasswordTokenGeneratorInterface $passwordTokenGenerator, MailingSystemInterface $mailer)  
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->userRepository = $userRepository;
        $this->passwordTokenGenerator = $passwordTokenGenerator;
        $this->mailer = $mailer;
    }

    public function reset(string $email, string $csrfToken)
    {
        $csrftoken = new CsrfToken('authenticate', $csrfToken);
        
        if (!$this->csrfTokenManager->isTokenValid($csrftoken)) {
            throw new InvalidCsrfTokenException('Invalid csrf token.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception("E-mail not found in database!");
        }

        $this->passwordTokenGenerator->generate($user);        

        $this->mailer->sendResetPasswordMessage($user);
    }

}
