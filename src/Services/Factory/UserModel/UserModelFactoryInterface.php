<?php declare(strict_types=1);

namespace App\Services\Factory\UserModel;

use App\Entity\User;
use App\Model\User\UserModel;

/**
 *  Manage creating of user models
 */
interface UserModelFactoryInterface
{   

    /**
     * create Create user model from user 
     * @param   User          $user     User object
     * @return  UserModel               Return filled in user model 
     */
    public function create(User $user): UserModel;
    
}
