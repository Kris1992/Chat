<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture
{

    /** @var UserPasswordEncoderInterface Password Encoder */
	private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function loadData(ObjectManager $manager)
    {

        $this->createMany(20, 'main_users', function($i)
        {
            $user = new User();
            $user
                ->setEmail(sprintf('user%d@example.com', $i)) 
                ->setLogin($this->faker->firstName.''.uniqid())
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    'admin01'
                ))
                ->setGender($this->faker->randomElement($array = array ('Male','Female')))
                ->setLastActivity(new \DateTime())
                ->agreeToTerms()
                ; 

            return $user;
        });

        //admins
        
        $this->createMany(3, 'admin_users', function($i) {
            $user = new User();
            $user
                ->setEmail(sprintf('admin%d@example.com', $i))
                ->setLogin($this->faker->firstName)
                ->setRoles(['ROLE_USER','ROLE_ADMIN'])
                ->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    'admin01'
                ))
                ->setGender($this->faker->randomElement($array = array ('Male','Female')))
                ->setLastActivity(new \DateTime())
                ->agreeToTerms()
                ;
            
            return $user;
        });


        $manager->flush();
    }

}
