<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * findOneByUsersInLastDay Find one report by users and in last 24 hours from given date
     * @param  User                 $reportSender   User object whose send report
     * @param  User                 $reportedUser   User object whose was reported
     * @param  \DateTimeInterface   $date           Date
     * @return Report[]
     */
    public function findOneByUsersInLastDay(User $reportSender, User $reportedUser, \DateTimeInterface $date)
    {   

    }
    
}
