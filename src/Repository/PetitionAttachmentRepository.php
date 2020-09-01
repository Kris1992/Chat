<?php

namespace App\Repository;

use App\Entity\PetitionAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetitionAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetitionAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetitionAttachment[]    findAll()
 * @method PetitionAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetitionAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetitionAttachment::class);
    }

    // /**
    //  * @return PetitionAttachment[] Returns an array of PetitionAttachment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PetitionAttachment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
