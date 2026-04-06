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

    public function findWithFilters(?string $search, ?string $type): array
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.materiel', 'm')
            ->orderBy('d.titre', 'ASC');

        if ($search) {
            $qb->andWhere('d.titre LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($type) {
            $qb->andWhere('d.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
