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

    public function findWithFilters(?string $search = null, ?string $type = null, ?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.utilisateur', 'u')->addSelect('u')
            ->leftJoin('a.emprunt', 'emp')->addSelect('emp')
            ->orderBy('a.createdAt', 'DESC');

        if ($userId) {
            $qb->andWhere('u.id = :uid')->setParameter('uid', $userId);
        }
        if ($search) {
            $qb->andWhere('a.message LIKE :s OR u.nom LIKE :s OR u.prenom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    public function findNonLues(?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.lu = false')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(10);

        if ($userId) {
            $qb->andWhere('a.utilisateur = :uid')->setParameter('uid', $userId);
        }

        return $qb->getQuery()->getResult();
    }

    public function countNonLues(?int $userId = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.lu = false');

        if ($userId) {
            $qb->andWhere('a.utilisateur = :uid')->setParameter('uid', $userId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
