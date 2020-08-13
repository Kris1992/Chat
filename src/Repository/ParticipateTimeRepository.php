<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParticipateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParticipateTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipateTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipateTime[]    findAll()
 * @method ParticipateTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipateTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipateTime::class);
    }

}
