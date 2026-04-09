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

    public function countEnAttente(?int $utilisateurId = null): int
    {
        if (!$utilisateurId) {
            return $this->count(['statut' => StatutReservation::EnAttente]);
        }
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.statut = :statut')
            ->andWhere('r.utilisateur = :uid')
            ->setParameter('statut', StatutReservation::EnAttente->value)
            ->setParameter('uid', $utilisateurId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPlanning(\DateTimeInterface $debut, \DateTimeInterface $fin, ?int $utilisateurId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->addSelect('m', 'u')
            ->join('r.materiel', 'm')
            ->join('r.utilisateur', 'u')
            ->where('r.dateDebut <= :fin AND r.dateFin >= :debut')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('r.statut != :annulee')
            ->setParameter('annulee', StatutReservation::Annulee->value)
            ->orderBy('r.dateDebut', 'ASC');

        if ($utilisateurId) {
            $qb->andWhere('r.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithFilters(?string $search, ?string $statut, ?int $utilisateurId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->addSelect('u', 'm')
            ->join('r.utilisateur', 'u')
            ->join('r.materiel', 'm')
            ->orderBy('r.dateDebut', 'DESC');

        if ($utilisateurId) {
            $qb->andWhere('r.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }
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
