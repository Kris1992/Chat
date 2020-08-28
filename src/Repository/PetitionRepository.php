<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\{Petition, User};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Petition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Petition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Petition[]    findAll()
 * @method Petition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Petition::class);
    }

    /**
     * findAllQuery Find all petitions or if searchTerms are not empty find petitions with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function findAllQuery(string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsQuery($searchTerms);
        }
        return $this->createQueryBuilder('p')
            ->join('p.petitioner', 'pu')
            ->addSelect('pu')
            ->getQuery()
            ;
    }

    /**
     * searchByTermsQuery Find petitions with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function searchByTermsQuery(string $searchTerms): Query
    {
        return $this->createQueryBuilder('p')
            ->join('p.petitioner', 'pu')
            ->addSelect('pu')
            ->andWhere('p.title LIKE :searchTerms OR pu.login LIKE :searchTerms OR p.type LIKE :searchTerms')
            ->setParameters([
                'searchTerms' => '%'.$searchTerms.'%'
            ])
            ->getQuery()
            ;
    }

    /**
     * findAllByUserQuery Find all petitions belongs to user or if searchTerms are not empty find petitions belongs to user with following data
     * @param  User     $user           User object
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function findAllByUserQuery(User $user, string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsAndUserQuery($user, $searchTerms);
        }
        return $this->createQueryBuilder('p')
            ->andWhere('p.petitioner = :user')
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ;
    }

    /**
     * searchByTermsAndUserQuery Find public chats with following data
     * @param  User     $user           User object
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function searchByTermsAndUserQuery(User $user, string $searchTerms): Query
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.petitioner = :user p.title LIKE :searchTerms OR p.type LIKE :searchTerms')
            ->setParameters([
                'user' => $user,
                'searchTerms' => '%'.$searchTerms.'%'
            ])
            ->getQuery()
            ;
    }

}
