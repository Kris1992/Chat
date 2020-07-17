<?php declare(strict_types=1);

namespace App\Services\UserRegistration;

use Symfony\Component\HttpFoundation\Request;
use App\Services\Factory\User\UserFactoryInterface;
use App\Model\User\UserModel;
use ReCaptcha\ReCaptcha;
use App\Entity\User;

class UserRegistration implements UserRegistrationInterface
{

    /** @var UserFactoryInterface */
    private $userFactory;

    /** @var ReCaptcha */
    private $reCaptcha;

    /**
     * UserRegistration Constructor
     * 
     * @param UserFactoryInterface $userFactory
     * @param ReCaptcha $reCaptcha
     */
    public function __construct(UserFactoryInterface $userFactory, ReCaptcha $reCaptcha) 
    {
        $this->userFactory = $userFactory;
        $this->reCaptcha = $reCaptcha;
    }

    public function register(Request $request, UserModel $userModel): User
    {
        /* In test env do not run captcha validation */
        if ($_ENV['APP_ENV'] !== 'test') {
            $isHuman = $this->checkCaptcha($request);

            if (!$isHuman->isSuccess()) {
                throw new \Exception('The ReCaptcha was not entered correctly!');
            }
        }

        return $this->userFactory->create($userModel, null, null);
    }

    private function checkCaptcha(Request $request)
    {
        /* If you run in localhost by symfony serve it will not work because of port */
        return $isHuman = $this->reCaptcha->setExpectedHostname($_SERVER['REMOTE_ADDR'])//$_SERVER['SERVER_NAME'])//$_SERVER['REMOTE_ADDR'])
                ->verify($request->get('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);
    }
    
}
