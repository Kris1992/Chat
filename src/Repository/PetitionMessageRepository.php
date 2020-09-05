<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\{PetitionMessage, Petition, User};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetitionMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetitionMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetitionMessage[]    findAll()
 * @method PetitionMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetitionMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetitionMessage::class);
    }

    /**
     * findUnreadedByPetitionAndOtherUser Find one report by users and between now and given date
     * @param  Petition             $petition       Petition object which is looking for
     * @param  User                 $user           User object whose will be omitted
     * @return PetitionMessage[]
     */
    public function findUnreadedByPetitionAndOtherUser(Petition $petition, User $user)
    {   
        return $this->createQueryBuilder('pm')
            ->andWhere('pm.petition = :petition AND pm.owner != :user AND pm.readedAt is NULL')
            ->setParameters([
                'petition' => $petition,
                'user' => $user,
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    

}
