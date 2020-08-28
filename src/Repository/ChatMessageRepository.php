<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\{ChatMessage, Chat};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
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

    /**
     * findByChatAndPeriods  Find messages by chat and between given dates and limit [default 10]
     * @param  Chat                 $chat           Chat object owner of messages
     * @param  \DateTimeInterface   $startDate      Start date of period to looking in
     * @param  \DateTimeInterface   $stopDate       Stop date of period to looking in
     * @param  \DateTimeInterface   $lastDate       Last date of message (don't get this message just all before it)
     * @param  int                  $limit          Integer with limit of messages to get [optional]
     * @return array                                Return array of Messages
     */
    public function findByChatAndPeriods(Chat $chat, \DateTimeInterface $startDate, \DateTimeInterface $stopDate, \DateTimeInterface $lastDate, int $limit = 10): array
    {   
        return $this->createQueryBuilder('m')
            ->andWhere('m.chat = :chat AND m.createdAt BETWEEN :startDate AND :stopDate AND m.createdAt < :lastDate')
            ->setParameters([
                'chat' => $chat,
                'startDate' => $startDate,
                'stopDate' => $stopDate,
                'lastDate' => $lastDate
            ])
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

}
