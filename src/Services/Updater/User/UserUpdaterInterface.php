<?php 
declare(strict_types=1);

namespace App\Services\Updater\User;

use App\Entity\User;
use App\Model\User\UserModel;
use Symfony\Component\HttpFoundation\File\File;

/** 
 *  Interface for updating User entities
 */
interface UserUpdaterInterface
{
    /**
     * update Update entity class with data from model class
     * @param UserModel $userModel  Model data class which will used to update 
     * entity
     * @param User $user            User class which will be updated
     * @param File $uploadedImage   File object with uploaded image [optional]
     * @return User                 Updated user
     */
    public function update(UserModel $userModel, User $user, ?File $uploadedImage): User;
}
