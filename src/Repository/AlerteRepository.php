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

    public function countNonLuesParUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.lu = false')
            ->andWhere('a.utilisateur = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNonLues(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('u', 'e', 'm')
            ->leftJoin('a.utilisateur', 'u')
            ->leftJoin('a.emprunt', 'e')
            ->leftJoin('e.materiel', 'm')
            ->where('a.lu = false')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countWithFilters(?string $search, ?string $type, ?int $utilisateurId = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->leftJoin('a.utilisateur', 'u');

        if ($utilisateurId) {
            $qb->andWhere('a.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }
        if ($search) {
            $qb->andWhere('a.message LIKE :s OR u.nom LIKE :s OR u.prenom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findWithFilters(?string $search, ?string $type, ?int $utilisateurId = null, ?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('u', 'e', 'm')
            ->leftJoin('a.utilisateur', 'u')
            ->leftJoin('a.emprunt', 'e')
            ->leftJoin('e.materiel', 'm')
            ->orderBy('a.createdAt', 'DESC');

        if ($utilisateurId) {
            $qb->andWhere('a.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }
        if ($search) {
            $qb->andWhere('a.message LIKE :s OR u.nom LIKE :s OR u.prenom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit)->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}
