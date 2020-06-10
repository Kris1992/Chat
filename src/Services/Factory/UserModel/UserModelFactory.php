<?php
declare(strict_types=1);

namespace App\Services\Factory\UserModel;

use App\Entity\User;
use App\Model\User\UserModel;

class UserModelFactory implements UserModelFactoryInterface 
{
    
    public function create(User $user): UserModel
    {
        
        $userModel = new UserModel();
        $userModel
            ->setId($user->getId())
            ->setEmail($user->getEmail())
            ->setLogin($user->getLogin())
            ->setGender($user->getGender())
            ->setRoles($user->getRoles())
            ->setImageFilename($user->getImageFilename())
            ;

        return $userModel;
    }
}
