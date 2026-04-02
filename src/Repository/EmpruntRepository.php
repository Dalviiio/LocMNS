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

    public function countEnCours(): int
    {
        return $this->count(['statut' => StatutEmprunt::EnCours]);
    }

    public function countRetards(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.statut = :en_cours OR e.statut = :retard')
            ->andWhere('e.dateFinPrevue < :now')
            ->setParameter('en_cours', StatutEmprunt::EnCours->value)
            ->setParameter('retard', StatutEmprunt::Retard->value)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findEnCours(): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->where('e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->orderBy('e.dateFinPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRetards(): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->where('e.statut = :retard')
            ->setParameter('retard', StatutEmprunt::Retard->value)
            ->orderBy('e.dateFinPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $search, ?string $statut): array
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->orderBy('e.dateDebut', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }
}
