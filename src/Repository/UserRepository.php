<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
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
     * @param  string   $searchTerms    Search word
     * @param  bool     $addReports     Is reports needed [optional]
     * @return Query
     */
    public function findAllQuery(string $searchTerms, bool $addReports = true): Query
    {   

        if ($searchTerms) {
            return $this->searchByTermsQuery($searchTerms, $addReports);
        }

        if ($addReports) {
            return $this->createQueryBuilder('u')
                ->leftJoin('u.reports', 'r', Expr\Join::WITH, 'r.createdAt BETWEEN :lastMonth AND :now')
                ->addSelect('COUNT(r.id) AS monthReports')
                ->setParameters([
                    'lastMonth' => new \Datetime('last month'),
                    'now' => new \DateTime('now'),
                ])
                ->groupBy('u.id')
                ->getQuery()
            ;
        }

        return $this->createQueryBuilder('u')
            ->getQuery()
        ;
    }

    /**
     * searchByTermsQuery Find all users with following data
     * @param  string   $searchTerms      Search word
     * @param  bool     $addReports     Is reports needed
     * @return Query
     */
    public function searchByTermsQuery(string $searchTerms, bool $addReports): Query
    {
        if ($addReports) {
            return $this->createQueryBuilder('u')
                ->where('u.email LIKE :searchTerms OR u.login LIKE :searchTerms')
                ->leftJoin('u.reports', 'r', Expr\Join::WITH, 'r.createdAt BETWEEN :lastMonth AND :now')
                ->addSelect('COUNT(r.id) AS monthReports')
                ->setParameters([
                    'searchTerms' => '%'.$searchTerms.'%',
                    'lastMonth' => new \Datetime('last month'),
                    'now' => new \DateTime('now'),
                ])
                ->groupBy('u.id')
                ->getQuery()
            ;
        }

        return $this->createQueryBuilder('u')
            ->where('u.email LIKE :searchTerms OR u.login LIKE :searchTerms')
            ->setParameters([
                'searchTerms' => '%'.$searchTerms.'%',
            ])
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
