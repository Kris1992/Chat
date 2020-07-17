<?php declare(strict_types=1);

namespace App\Services\UserRegistration;

use Symfony\Component\HttpFoundation\Request;
use App\Model\User\UserModel;
use App\Entity\User;

/**
 *  Take care about user registration process 
 */
interface UserRegistrationInterface
{   
    /**
     * register Take care about user registration process and return user 
     * @param  Request                   $request
     * @param  UserModel                 $userModel     Validated user model
     * @throws Exception                                Throw Exception when captcha isn't correct
     * @return User
     */
    public function register(Request $request, UserModel $userModel): User;
}
