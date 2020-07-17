<?php declare(strict_types=1);

namespace App\Services\Updater\User;

use App\Entity\User;
use App\Model\User\UserModel;
use Symfony\Component\HttpFoundation\File\File;
use App\Services\ImagesManager\ImagesManagerInterface;

class UserUpdater implements UserUpdaterInterface 
{   

    /** @var ImagesManagerInterface */
    private $userImagesManager;

    /**
     * UserUpdater Constructor
     * 
     * @param ImagesManagerInterface $userImagesManager
     */
    public function __construct(ImagesManagerInterface $userImagesManager)  
    {
        $this->userImagesManager = $userImagesManager;
    }

    public function update(UserModel $userModel, User $user, ?File $uploadedImage): User
    {

        $user
            ->setEmail($userModel->getEmail())
            ->setGender($userModel->getGender())
            ->setRoles($userModel->getRoles())
            ;
        
        if ($uploadedImage) {
            $newFilename = $this->userImagesManager->uploadImage($uploadedImage, $user->getImageFilename(), $user->getLogin());
            
            if ($newFilename) {
                $user->setImageFilename($newFilename);
            }
        }
        
        return $user;
    }
}
