<?php
declare(strict_types=1);

namespace App\Services\Updater\User;

use App\Entity\User;
use App\Model\User\UserModel;
use Symfony\Component\HttpFoundation\File\File;

class UserUpdater implements UserUpdaterInterface 
{

    public function update(UserModel $userModel, User $user, ?File $uploadedImage): User
    {

        $user
            ->setEmail($userModel->getEmail())
            ->setLogin($userModel->getLogin())
            ->setGender($userModel->getGender())
            ->setRoles($userModel->getRoles())
            ;
        
        return $user;
    }
}
