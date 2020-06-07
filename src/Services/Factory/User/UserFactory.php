<?php
declare(strict_types=1);

namespace App\Services\Factory\User;

use App\Entity\User;
use App\Model\User\UserModel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFactory implements UserFactoryInterface 
{

    /** @var UserPasswordEncoderInterface Password encoder */
    private $passwordEncoder;

    /**
     * UserFactory Constructor
     * 
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImagesManagerInterface $imagesManager
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder/*, ImagesManagerInterface $imagesManager*/)  
    {
        $this->passwordEncoder = $passwordEncoder;
        /*$this->imagesManager = $imagesManager;*/
    }
    
    public function create(UserModel $userModel, ?string $role, ?File $uploadedImage): User
    {
        if (!$role) {
            $role = 'ROLE_USER';
        }
        
        $user = new User();
        $user
            ->setEmail($userModel->getEmail())
            ->setLogin($userModel->getLogin())
            ->setGender($userModel->getGender())
            ->setRoles([$role])
            ->setLastActivity(new \DateTime())
            ;

        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $userModel->getPlainPassword()
        ));

        /*if ($uploadedImage) {
            $newFilename = $this->imagesManager->uploadImage($uploadedImage, null, $user->getLogin());
            $user->setImageFilename($newFilename);
        }*/

        if ($userModel->getAgreeTerms()) {
            $user->agreeToTerms();
        } else {
            throw new \Exception("You not agree terms?");
        }

        return $user;
    }

}
