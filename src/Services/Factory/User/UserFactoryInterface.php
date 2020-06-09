<?php
declare(strict_types=1);

namespace App\Services\Factory\User;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\User\UserModel;
use App\Entity\User;

/**
 *  Take care about creating users
 */
interface UserFactoryInterface
{   

    /**
     * create Create user 
     * @param UserModel     $userModel      Model with user data
     * @param Array|null    $roles          Array with role names [optional]
     * @param File|null     $uploadedImage  Uploaded image [optional]
     * @throws Exception                    Throw Exception when user doesn't accept terms
     * @return User
     */
    public function create(UserModel $userModel, ?array $roles, ?File $uploadedImage): User;

}
