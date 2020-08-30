<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\BaseAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BaseAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method BaseAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method BaseAttachment[]    findAll()
 * @method BaseAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseAttachment::class);
    }

}
