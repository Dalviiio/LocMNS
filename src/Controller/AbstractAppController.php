<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAppController extends AbstractController
{
    public function __construct(protected UtilisateurRepository $utilisateurRepo) {}

    protected function getUtilisateurConnecte(Request $request): Utilisateur
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            throw new \RuntimeException('Utilisateur non connecté.');
        }

        $user = $this->utilisateurRepo->find($userId);
        if (!$user) {
            throw new \RuntimeException('Utilisateur introuvable en base de données.');
        }

        return $user;
    }

    protected function getProfilContext(Request $request): array
    {
        $profil = $request->getSession()->get('user_profil', '');

        return [
            'profilNom'      => $profil,
            'isAdmin'        => $profil === 'Administrateur',
            'isGestionnaire' => in_array($profil, ['Administrateur', 'Gestionnaire'], true),
        ];
    }
}
