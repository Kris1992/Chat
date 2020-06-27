<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\{Friend, User};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    /**
     * createFriendsByInviteeCriteria Returns Friend object where invitee is given user and status is not rejected
     * @param  User   $user     User object whose is or not invited by current one
     * @return Criteria
     */
    public static function createNotRejectedFriendsByInviteeCriteria(User $user): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->andX(Criteria::expr()->eq('invitee', $user), Criteria::expr()->neq('status', 'Rejected')))
        ;
    }

    /**
     * createFriendsByInviterCriteria Returns Friend object where inviter is given user and status is not rejected
     * @param  User   $user     User object whose invite or not current one
     * @return Criteria
     */
    public static function createNotRejectedFriendsByInviterCriteria(User $user): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->andX(Criteria::expr()->eq('inviter', $user), Criteria::expr()->neq('status', 'Rejected')))
        ;
    }

    /**
     * findAllQueryByStatus Find all friends with given status or if searchTerms are not empty find all friends with following data and given status
     * @param  string   $searchTerms    Search word
     * @param  User     $currentUser    User object of current user (do not include it in friends list)
     * @param  string   $status         String with status
     * @return Query
     */
    public function findAllQueryByStatus(string $searchTerms, User $currentUser, string $status): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsAndStatusQuery($searchTerms, $currentUser, $status);
        }
        return $this->createQueryBuilder('f')
            ->andWhere('(f.inviter = :inviter OR f.invitee = :invitee) 
                AND f.status = :status')
            ->setParameters([
                'inviter' => $currentUser,
                'invitee' => $currentUser,
                'status' => $status,
            ])
            ->getQuery()
        ;
        
    }

    /**
     * searchByTermsAndStatusQuery Find all users with following data
     * @param  string   $searchTerms    Search word
     * @param  User     $currentUser    User object of current user (do not include it in friends list)
     * @param  string   $status         String with status
     * @return Query
     */
    public function searchByTermsAndStatusQuery(string $searchTerms, User $currentUser, string $status):Query
    {
        return $this->createQueryBuilder('f')
            ->join('f.inviter', 'u')
            ->addSelect('u')
            ->join('f.invitee', 'u2')
            ->addSelect('u2')
            ->andWhere('
                ((f.inviter = :currentUser OR f.invitee = :currentUser) 
                AND f.status = :status) AND ((u.email LIKE :searchTerms) OR (u.login LIKE :searchTerms) OR (u2.email LIKE :searchTerms) OR (u2.login LIKE :searchTerms))')
            ->setParameters([
                'currentUser' => $currentUser,
                'status' => $status,
                'searchTerms' => '%'.$searchTerms.'%',
            ])
            ->getQuery()
        ;
    }

}
