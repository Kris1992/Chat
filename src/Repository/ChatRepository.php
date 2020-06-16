<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    /**
     * findPublicChatsQuery Find public chats or if searchTerms are not empty find public chats with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function findPublicChatsQuery(string $searchTerms): Query
    {   
        if ($searchTerms) {
            return $this->searchByTermsQuery($searchTerms);
        }
        return $this->createQueryBuilder('c')
            ->andWhere('c.isPublic = :public')
            ->setParameter('public', true)
            ->getQuery()
            ;
    }

    /**
     * searchByTermsQuery Find public chats with following data
     * @param  string $searchTerms Search word
     * @return Query
     */
    public function searchByTermsQuery(string $searchTerms): Query
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isPublic = :public AND c.title LIKE :searchTerms')
            ->setParameters([
                'searchTerms' => '%'.$searchTerms.'%',
                'public' => true
            ])
            ->getQuery()
            ;
    }

    /**
     * findAllPublicByIds Find public chats with given ids
     * @param  array  $arrayIds Array with at least one id
     * @return Chat[]
     */
    public function findAllPublicByIds(array $arrayIds)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id IN(:ids) AND c.isPublic = :isPublic')
            ->setParameters([
                'ids' => $arrayIds,
                'isPublic' => true
            ])
            ->getQuery()
            ->getResult()
            ;
    }

}
