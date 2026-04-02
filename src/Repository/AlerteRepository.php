<?php

namespace App\Repository;

use App\Entity\Alerte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AlerteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alerte::class);
    }

    public function countNonLues(): int
    {
        return $this->count(['lu' => false]);
    }

    public function findNonLues(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.lu = false')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $search, ?string $type): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.utilisateur', 'u')
            ->orderBy('a.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('a.message LIKE :s OR u.nom LIKE :s OR u.prenom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
