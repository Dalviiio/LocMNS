<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findByMateriel(int $materielId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.materiel = :m')
            ->setParameter('m', $materielId)
            ->orderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
