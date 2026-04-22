<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\StatutReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findWithFilters(?string $search = null, ?string $statut = null, ?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.materiel', 'm')->addSelect('m')
            ->leftJoin('r.utilisateur', 'u')->addSelect('u')
            ->orderBy('r.dateDebut', 'DESC');

        if ($userId) {
            $qb->andWhere('u.id = :uid')->setParameter('uid', $userId);
        }
        if ($search) {
            $qb->andWhere('m.nom LIKE :s OR u.nom LIKE :s OR u.prenom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }

    public function findPlanning(int $mois, int $annee): array
    {
        $debut = new \DateTime("$annee-$mois-01");
        $fin   = (clone $debut)->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->leftJoin('r.materiel', 'm')->addSelect('m')
            ->leftJoin('r.utilisateur', 'u')->addSelect('u')
            ->andWhere('r.dateDebut BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('r.dateDebut', 'ASC')
            ->getQuery()->getResult();
    }

    public function countEnAttente(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.statut = :s')
            ->setParameter('s', StatutReservation::EnAttente)
            ->getQuery()->getSingleScalarResult();
    }
}
