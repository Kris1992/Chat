<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * createBetweenDatesCriteria Returns messages between dates
     * @param \DateTimeInterface    $startDate      Start date of messages to get
     * @param \DateTimeInterface    $stopDate       Stop date of messages to get
     * @return Criteria
     */
    public static function createBetweenDatesCriteria(\DateTimeInterface $startDate, \DateTimeInterface $stopDate): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->andX(Criteria::expr()->gte('createdAt', $startDate), Criteria::expr()->lte('createdAt', $stopDate)))
        ;
    }

}
