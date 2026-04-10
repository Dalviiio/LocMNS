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

    public function countEnCours(?int $utilisateurId = null): int
    {
        if (!$utilisateurId) {
            return $this->count(['statut' => StatutEmprunt::EnCours]);
        }
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.statut = :statut')
            ->andWhere('e.utilisateur = :uid')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->setParameter('uid', $utilisateurId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countRetards(?int $utilisateurId = null): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.statut = :en_cours OR e.statut = :retard')
            ->andWhere('e.dateFinPrevue < :now')
            ->setParameter('en_cours', StatutEmprunt::EnCours->value)
            ->setParameter('retard', StatutEmprunt::Retard->value)
            ->setParameter('now', new \DateTime());

        if ($utilisateurId) {
            $qb->andWhere('e.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findEnCours(?int $utilisateurId = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('u', 'm')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->where('e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->orderBy('e.dateFinPrevue', 'ASC');

        if ($utilisateurId) {
            $qb->andWhere('e.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findRetards(): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('u', 'm')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->where('e.statut = :retard')
            ->setParameter('retard', StatutEmprunt::Retard->value)
            ->orderBy('e.dateFinPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countWithFilters(?string $search, ?string $statut, ?int $utilisateurId = null): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm');

        if ($utilisateurId) {
            $qb->andWhere('e.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }
        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findWithFilters(?string $search, ?string $statut, ?int $utilisateurId = null, ?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('u', 'm')
            ->join('e.utilisateur', 'u')
            ->join('e.materiel', 'm')
            ->orderBy('e.dateDebut', 'DESC');

        if ($utilisateurId) {
            $qb->andWhere('e.utilisateur = :uid')->setParameter('uid', $utilisateurId);
        }
        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR m.nom LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit)->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}
