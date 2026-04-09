<?php

namespace App\Service;

use App\Entity\Materiel;
use App\Entity\Profil;
use App\Repository\ProfilRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AutorisationService
{
    public function __construct(
        private RequestStack $requestStack,
        private UtilisateurRepository $utilisateurRepo,
        private ProfilRepository $profilRepo,
    ) {}

    public function getProfilConnecte(): ?Profil
    {
        $userId = $this->requestStack->getSession()->get('user_id');
        if (!$userId) return null;
        $utilisateur = $this->utilisateurRepo->find($userId);
        return $utilisateur?->getProfil();
    }

    /** Retourne les IDs des catégories autorisées pour le profil connecté */
    public function getCategoriesAutorisees(): array
    {
        $profil = $this->getProfilConnecte();
        if (!$profil) return [];
        return $profil->getCategories()->map(fn($c) => $c->getId())->toArray();
    }

    /** Vérifie qu'un matériel est accessible, lève une exception sinon */
    public function verifierAccesMateriel(Materiel $materiel): void
    {
        $profil = $this->getProfilConnecte();
        if (!$profil) return;

        // Administrateur et Gestionnaire ont accès à tout
        if (in_array($profil->getNom(), ['Administrateur', 'Gestionnaire'])) return;

        $categoriesAutorisees = $this->getCategoriesAutorisees();
        if ($materiel->getCategorie() && !in_array($materiel->getCategorie()->getId(), $categoriesAutorisees)) {
            throw new AccessDeniedHttpException(
                'Vous n\'avez pas accès au matériel "' . $materiel->getNom() . '" avec votre profil ' . $profil->getNom() . '.'
            );
        }
    }

    public function isAdmin(): bool
    {
        $profil = $this->getProfilConnecte();
        return $profil?->getNom() === 'Administrateur';
    }

    public function isAdminOrGestionnaire(): bool
    {
        $profil = $this->getProfilConnecte();
        return in_array($profil?->getNom(), ['Administrateur', 'Gestionnaire']);
    }

    public function getNomProfil(): string
    {
        return $this->getProfilConnecte()?->getNom() ?? '';
    }

    public function getUtilisateurConnecte(): ?object
    {
        $userId = $this->requestStack->getSession()->get('user_id');
        if (!$userId) return null;
        return $this->utilisateurRepo->find($userId);
    }

    public function getUserId(): ?int
    {
        return $this->requestStack->getSession()->get('user_id');
    }

    /**
     * Lève AccessDeniedHttpException si le profil connecté n'est pas dans la liste autorisée.
     * @param string[] $profilsAutorises
     */
    public function verifier(array $profilsAutorises, string $message = 'Accès non autorisé.'): void
    {
        if (!in_array($this->getNomProfil(), $profilsAutorises)) {
            throw new AccessDeniedHttpException($message);
        }
    }
}
