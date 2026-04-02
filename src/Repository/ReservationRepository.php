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

    public function countEnAttente(): int
    {
        return $this->count(['statut' => StatutReservation::EnAttente]);
    }

    public function findPlanning(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.materiel', 'm')
            ->join('r.utilisateur', 'u')
            ->where('r.dateDebut <= :fin AND r.dateFin >= :debut')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('r.statut != :annulee')
            ->setParameter('annulee', StatutReservation::Annulee->value)
            ->orderBy('r.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $search, ?string $statut): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.utilisateur', 'u')
            ->join('r.materiel', 'm')
            ->orderBy('r.dateDebut', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }
}
