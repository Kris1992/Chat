<?php declare(strict_types=1);

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
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)  
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function create(UserModel $userModel, ?array $roles, ?File $uploadedImage): User
    {
        if (!$roles) {
            $roles = ['ROLE_USER'];
        }
        
        $user = new User();
        $user
            ->setEmail($userModel->getEmail())
            ->setLogin($userModel->getLogin())
            ->setGender($userModel->getGender())
            ->setRoles($roles)
            ->setLastActivity(new \DateTime())
            ;

        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $userModel->getPlainPassword()
        ));

        if ($userModel->getAgreeTerms()) {
            $user->agreeToTerms();
        } else {
            throw new \Exception("You not agree terms?");
        }

        return $user;
    }

}
