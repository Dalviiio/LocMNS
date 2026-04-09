<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\ProfilRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AutorisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/utilisateurs', name: 'utilisateur_')]
class UtilisateurController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, UtilisateurRepository $repo, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs'  => $repo->findWithFilters($search ?: null),
            'filtre_search' => $search,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ProfilRepository $profilRepo, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_user', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $utilisateur = new Utilisateur();
            $this->hydrateFromRequest($utilisateur, $request, $profilRepo);
            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/form.html.twig', [
            'utilisateur' => null,
            'profils'     => $profilRepo->findAll(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $em, ProfilRepository $profilRepo, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_user' . $utilisateur->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $this->hydrateFromRequest($utilisateur, $request, $profilRepo, preservePassword: true);
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/form.html.twig', [
            'utilisateur' => $utilisateur,
            'profils'     => $profilRepo->findAll(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete_user' . $utilisateur->getId(), $request->request->get('_token'))) {
            $em->remove($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('utilisateur_index');
    }

    private function hydrateFromRequest(Utilisateur $utilisateur, Request $request, ProfilRepository $profilRepo, bool $preservePassword = false): void
    {
        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $utilisateur->setEmail($request->request->get('email'));

        $mdp = $request->request->get('mot_de_passe');
        if ($mdp && (!$preservePassword || $mdp !== '')) {
            $utilisateur->setMotDePasse(password_hash($mdp, PASSWORD_BCRYPT));
        }

        $profil = $profilRepo->find($request->request->getInt('profil_id'));
        if ($profil) {
            $utilisateur->setProfil($profil);
        }
    }
}
