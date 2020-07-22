<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\{Participant, Chat, User};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * createNotIncludedUserCriteria Returns Participants of chat without given user
     * @param  User     $user   User object which should be not included to participants list
     * @return Criteria
     */
    public static function createNotIncludedUserCriteria(User $user): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->neq('user', $user))
        ;
    }

    /**
     * findAllOthersParticipantsByChat Find all participants of chat room without given one
     * @param  User     $user   User object which will be ignored    
     * @param  Chat     $chat   Chat object which will be looking into
     * @return Participant[]
     */
    public function findAllOthersParticipantsByChat(User $user, Chat $chat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.user != :user')
            ->setParameters([
                'chat' => $chat,
                'user' => $user
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * findParticipantByUserAndChat Find participant of chat room by chat and user object
     * @param  User     $user   User object which is participant of chat
     * @param  Chat     $chat   Chat object which will be looking into
     * @return Participant|null
     */
    public function findParticipantByUserAndChat(User $user, Chat $chat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.user = :user')
            ->setParameters([
                'chat' => $chat,
                'user' => $user
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    
}
