<?php

namespace App\Service;

use App\Entity\Alerte;
use App\Entity\Emprunt;
use App\Entity\TypeAlerte;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class AlerteService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * Crée et persiste une alerte (sans flush — le flush reste à la charge du controller).
     */
    public function creer(
        Utilisateur $utilisateur,
        string      $message,
        TypeAlerte  $type,
        ?Emprunt    $emprunt = null,
    ): Alerte {
        $alerte = new Alerte();
        $alerte->setUtilisateur($utilisateur);
        $alerte->setMessage($message);
        $alerte->setType($type);

        if ($emprunt !== null) {
            $alerte->setEmprunt($emprunt);
        }

        $this->em->persist($alerte);

        return $alerte;
    }
}
