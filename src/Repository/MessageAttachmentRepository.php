<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageAttachment[]    findAll()
 * @method MessageAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageAttachment::class);
    }

}
