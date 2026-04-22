<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\StatutEmprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    public function findWithFilters(?string $search = null, ?string $statut = null, ?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.utilisateur', 'u')->addSelect('u')
            ->leftJoin('e.materiel', 'm')->addSelect('m')
            ->orderBy('e.dateDebut', 'DESC');

        if ($userId) {
            $qb->andWhere('u.id = :uid')->setParameter('uid', $userId);
        }
        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }

    public function findEnCours(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.utilisateur', 'u')->addSelect('u')
            ->leftJoin('e.materiel', 'm')->addSelect('m')
            ->andWhere('e.statut = :s')
            ->setParameter('s', StatutEmprunt::EnCours)
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()->getResult();
    }

    public function countRetards(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.statut != :s')
            ->andWhere('e.dateFinPrevue < :now')
            ->setParameter('s', StatutEmprunt::Rendu)
            ->setParameter('now', new \DateTime())
            ->getQuery()->getSingleScalarResult();
    }

    public function findPlanningDashboard(): array
    {
        $debut = new \DateTime('monday this week');
        $fin   = new \DateTime('sunday this week');
        $fin->setTime(23, 59, 59);

        return $this->createQueryBuilder('e')
            ->leftJoin('e.utilisateur', 'u')->addSelect('u')
            ->leftJoin('e.materiel', 'm')->addSelect('m')
            ->andWhere('e.dateDebut BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()->getResult();
    }
}
