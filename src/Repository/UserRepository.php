<?php declare(strict_types=1);

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
            ->leftJoin('u.reports', 'r')
            ->addSelect('r')
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
            ->leftJoin('u.reports', 'r')
            ->addSelect('r')
            ->getQuery()
        ;
    }

    /**
     * findUsersLastActicity Find users by given ids
     * @param  array  $usersIds Array with ids of users to find
     * @return array            Return array with ids and last activity datetime
     */
    public function findUsersLastActicity(array $usersIds): array
    {   
        return $this->createQueryBuilder('u')
            ->select('u.id, u.lastActivity')
            ->andWhere('u.id IN(:usersIds)')
            ->setParameter('usersIds', $usersIds)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * findAllByIds Find all users with given ids
     * @param  array  $arrayIds Array with at least one id
     * @return User[]
     */
    public function findAllByIds(array $arrayIds)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id IN(:ids)')
            ->setParameter('ids', $arrayIds)
            ->getQuery()
            ->getResult()
        ;
    }

}
