<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\PetitionMessage;
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

}
