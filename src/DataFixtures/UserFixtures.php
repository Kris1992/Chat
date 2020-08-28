<?php declare(strict_types=1);

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Services\ImagesManager\ImagesManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends BaseFixture
{

    /** @var UserPasswordEncoderInterface */
	private $passwordEncoder;

    /** @var ImagesManagerInterface */
    private $userImageManager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ImagesManagerInterface $userImageManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userImageManager = $userImageManager;
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

            $user = $this->randomizeUploadImage($user);

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

            $user = $this->randomizeUploadImage($user);

            return $user;
        });


        $manager->flush();
    }

    private function randomizeUploadImage(User $user, int $chanceTrue = 50): User
    {
        //In test env we do need waste of time to upload images
        if ($_ENV['APP_ENV'] !== 'test') {
            if ($this->faker->boolean($chanceTrue)) {
                $imageFilename = $this->uploadFakeImage($user->getLogin());
                $user
                    ->setImageFilename($imageFilename)
                ;
            }
        }

        return $user;
    }

    private function uploadFakeImage(string $subdirectory): string
    {
        $randomImage = 'image'.$this->faker->numberBetween(0, 3).'.jpg';
        $imagePath = __DIR__.'/users_images/'.$randomImage;

        return $this->userImageManager
            ->uploadImage(new File($imagePath), null, $subdirectory)
            ;
    }

}
