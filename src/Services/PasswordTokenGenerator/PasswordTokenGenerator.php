<?php declare(strict_types=1);

namespace App\Services\PasswordTokenGenerator;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{User, PasswordToken};

class PasswordTokenGenerator implements PasswordTokenGeneratorInterface 
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * PasswordTokenGenerator Constructor
     * 
     * @param EntityManagerInterface    $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)  
    {
        $this->entityManager = $entityManager;
    }

    public function generate(User $user): PasswordToken
    {
        $passTokenOld = $user->getPasswordToken();

        if($passTokenOld) {
            $this->entityManager->remove($passTokenOld);
        }

        $passToken = new PasswordToken($user);
        $user->setPasswordToken($passToken);
        $this->entityManager->persist($passToken);
        $this->entityManager->flush();

        return $passToken;
    }

}
