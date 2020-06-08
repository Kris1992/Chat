<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\PasswordToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PasswordToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordToken[]    findAll()
 * @method PasswordToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordToken::class);
    }

}
