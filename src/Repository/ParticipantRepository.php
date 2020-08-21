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
            //and not removed
        ;
    }

    /**
     * createUserCriteria Returns Participant of chat with given user
     * @param  User     $user   User object which should be included to participant
     * @return Criteria
     */
    public static function createUserCriteria(User $user): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('user', $user))
            //->setMaxResults(1)
        ;
    }

    /**
     * findAllOthersParticipantsByChat Find all participants of chat room without given one
     * @param  User     $user           User object which will be ignored    
     * @param  Chat     $chat           Chat object which will be looking into
     * @param  bool     $isRemoved      Boolean with is removed state [optional]
     * @return Participant[]
     */
    public function findAllOthersParticipantsByChat(User $user, Chat $chat, bool $isRemoved = false)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.user != :user AND p.isRemoved = :isRemoved')
            ->setParameters([
                'chat' => $chat,
                'user' => $user,
                'isRemoved' => $isRemoved
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

    /**
     * findParticipantByUserAndChatAndRemovedState Find participant of chat room by chat, user object and isRemoved state
     * @param  User     $user           User object which is participant of chat
     * @param  Chat     $chat           Chat object which will be looking into
     * @param  bool     $isRemoved      Boolean with isRemoved state [optional]
     * @return Participant|null
     */
    public function findParticipantByUserAndChatAndRemovedState(User $user, Chat $chat, bool $isRemoved = false)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.user = :user AND p.isRemoved = :isRemoved')
            ->setParameters([
                'chat' => $chat,
                'user' => $user,
                'isRemoved' => $isRemoved
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * findAllByUsersAndChat Find all participants of chat room by chat and given users
     * @param  array    $users  Array with users objects 
     * @param  Chat     $chat   Chat object which will be looking into
     * @return Participant[]
     */
    public function findAllByUsersAndChat(array $users, Chat $chat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.user IN(:users)')
            ->setParameters([
                'chat' => $chat,
                'users' => $users
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * findAllByIdsAndChat Find all participants of chat room by chat and given ids
     * @param  array    $participantsIds    Array with participants ids
     * @param  Chat     $chat               Chat object which will be looking into
     * @return Participant[]
     */
    public function findAllByIdsAndChat(array $participantsIds, Chat $chat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.chat = :chat AND p.id IN(:participantsIds)')
            ->setParameters([
                'chat' => $chat,
                'participantsIds' => $participantsIds
            ])
            ->getQuery()
            ->getResult()
            ;
    }
    
}
