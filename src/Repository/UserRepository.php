<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * findAllQuery Find all Users or if searchTerms are not empty find all users with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function findAllQuery(string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsQuery($searchTerms);
        }
        return $this->createQueryBuilder('u')
            ->getQuery()
        ;
    }

    /**
     * searchByTermsQuery Find all users with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function searchByTermsQuery(string $searchTerms): Query
    {
        return $this->createQueryBuilder('u')
            ->where('u.email LIKE :searchTerms OR u.login LIKE :searchTerms')
            ->setParameters([
                'searchTerms' => '%'.$searchTerms.'%'
            ])
            ->getQuery()
        ;
    }

}
