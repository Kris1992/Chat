<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;
use App\Entity\{Report, User};
use Doctrine\ORM\Query;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * findAllQuery Find all reports or if searchTerms are not empty find all reports with following data
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function findAllQuery(string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsQuery($searchTerms);
        }
        return $this->createQueryBuilder('r')
            ->join('r.reportSender', 'rs')
            ->addSelect('rs')
            ->join('r.reportedUser', 'ru')
            ->addSelect('ru')
            ->getQuery()
        ;
    }

    /**
     * searchByTermsQuery Find all reports with following data
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function searchByTermsQuery(string $searchTerms): Query
    {
        return $this->createQueryBuilder('r')
            ->where('r.description LIKE :searchTerms OR r.type LIKE :searchTerms')
            ->setParameters([
                'searchTerms' => '%'.$searchTerms.'%'
            ])
            ->join('r.reportSender', 'rs')
            ->addSelect('rs')
            ->join('r.reportedUser', 'ru')
            ->addSelect('ru')
            ->getQuery()
        ;
    }

    /**
     * findReportsQueryByUser Find all reports by reported user or if searchTerms are not empty find all reports by reported user with following data
     * @param  User     $reportedUser   User object
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function findReportsQueryByUser(User $reportedUser, string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsAndUserQuery($reportedUser, $searchTerms);
        }
        return $this->createQueryBuilder('r')
            ->where('r.reportedUser = :reportedUser')
            ->setParameter('reportedUser', $reportedUser)
            ->join('r.reportSender', 'rs')
            ->addSelect('rs')
            ->getQuery()
        ;
    }

    /**
     * searchByTermsAndUserQuery Find all reports by reported user with following data
     * @param  User     $reportedUser   User object
     * @param  string   $searchTerms    Search word
     * @return Query
     */
    public function searchByTermsAndUserQuery(User $reportedUser, string $searchTerms): Query
    {
        return $this->createQueryBuilder('r')
            ->where('r.reportedUser = :reportedUser AND (r.description LIKE :searchTerms OR r.type LIKE :searchTerms)')
            ->setParameters([
                'reportedUser' => $reportedUser,
                'searchTerms' => '%'.$searchTerms.'%'
            ])
            ->join('r.reportSender', 'rs')
            ->addSelect('rs')
            ->getQuery()
        ;
    }

    /**
     * findOneByUsersAfterDate Find one report by users and between now and given date
     * @param  User                 $reportSender   User object whose send report
     * @param  User                 $reportedUser   User object whose was reported
     * @param  \DateTimeInterface   $date           Date
     * @return Report[]
     */
    public function findOneByUsersAfterDate(User $reportSender, User $reportedUser, \DateTimeInterface $date)
    {   
        return $this->createQueryBuilder('r')
            ->andWhere('r.reportSender = :reportSender AND r.reportedUser = :reportedUser AND r.createdAt > :date')
            ->setParameters([
                'reportSender' => $reportSender,
                'reportedUser' => $reportedUser,
                'date' => $date
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    
}
