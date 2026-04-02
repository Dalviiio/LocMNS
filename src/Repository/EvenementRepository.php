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
        return (int) $this->createQueryBuilder('ev')
            ->select('COUNT(ev.id)')
            ->where('ev.type IN (:types)')
            ->setParameter('types', [
                TypeEvenement::Panne->value,
                TypeEvenement::Dysfonctionnement->value,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
