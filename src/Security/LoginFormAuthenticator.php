<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\{UserInterface, UserProviderInterface};
use Symfony\Component\Security\Csrf\{CsrfToken, CsrfTokenManagerInterface};
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private const ATTEMPTS_LIMIT = 5;

    public const LOGIN_ROUTE = 'app_login';

    /** @var EntityManagerInterface */
    private $entityManager;
    
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var UserPasswordEncoderInterface Password Encoder */
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'plainPassword' => $request->request->get('plainPassword'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {   
        if ($user->getBanTo() > new \DateTime()) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Your account is temporary banned to %s.', $user->getBanTo()->format('Y-m-d H:i:s'))
            );
        }

        if($user->getFailedAttempts() < self::ATTEMPTS_LIMIT) {

            $isPasswordValid = $this->passwordEncoder->isPasswordValid($user, $credentials['plainPassword']);

            if(!$isPasswordValid) {

                $failedAttempts = $user->increaseFailedAttempts();
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                throw new CustomUserMessageAuthenticationException(
                    sprintf('It is your %d failed attempt to log in from %d available ', $failedAttempts, self::ATTEMPTS_LIMIT)
                );

            } else {
                $user->resetFailedAttempts();
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            return $isPasswordValid;

        } else {
             
            throw new CustomUserMessageAuthenticationException(sprintf('Account blocked due to %d failed login attempts! Unlock by forgot password section', self::ATTEMPTS_LIMIT));
        }

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        //zmienic app_homepage
        return new RedirectResponse($this->urlGenerator->generate('app_homepage'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
