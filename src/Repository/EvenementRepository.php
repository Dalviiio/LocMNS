<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\TypeEvenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function countIncidentsOuverts(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.type IN (:types)')
            ->setParameter('types', [TypeEvenement::Panne, TypeEvenement::Dysfonctionnement])
            ->getQuery()->getSingleScalarResult();
    }
}
