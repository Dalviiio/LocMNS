<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findByProfil(int $profilId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.profil = :profil')
            ->setParameter('profil', $profilId)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $search): array
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.profil', 'p')
            ->orderBy('u.nom', 'ASC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR u.email LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
