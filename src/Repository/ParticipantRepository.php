<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\{Participant, Chat, User};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * findAllOthersParticipantsByChat Find all participants of chat room without given one
     * @param  User     $user   User object which will be ignored    
     * @param  Chat     $chat   Chat object which will be looking into
     * @return Participant[]
     */
    public function findAllOthersParticipantsByChat(User $user,Chat $chat)
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
}
