<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\ProfilRepository;
use App\Repository\UtilisateurRepository;
use App\Service\RoleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/utilisateurs')]
class UtilisateurController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private ProfilRepository      $profilRepo,
        private RoleService           $roleService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'utilisateur_index')]
    public function index(Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);

        return $this->render('utilisateur/index.html.twig', array_merge(
            $this->getProfilContext($request),
            ['utilisateurs' => $this->utilisateurRepo->findBy([], ['nom' => 'ASC'])]
        ));
    }

    #[Route('/nouveau', name: 'utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);
        $context = $this->getProfilContext($request);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('user_new', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            if ($this->utilisateurRepo->findByEmail($request->request->get('email'))) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('utilisateur_new');
            }

            $profil = $this->profilRepo->find($request->request->getInt('profil_id'));
            if (!$profil) { $this->addFlash('error', 'Profil invalide.'); return $this->redirectToRoute('utilisateur_new'); }

            $u = new Utilisateur();
            $u->setNom($request->request->get('nom'));
            $u->setPrenom($request->request->get('prenom'));
            $u->setEmail($request->request->get('email'));
            $u->setMotDePasse(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
            $u->setProfil($profil);
            $this->em->persist($u);
            $this->em->flush();

            $this->addFlash('success', 'Utilisateur créé.');
            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/new.html.twig', array_merge($context, [
            'profils' => $this->profilRepo->findBy([], ['nom' => 'ASC']),
        ]));
    }

    #[Route('/{id}/modifier', name: 'utilisateur_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);
        $context = $this->getProfilContext($request);

        $cible = $this->utilisateurRepo->find($id);
        if (!$cible) { throw $this->createNotFoundException(); }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('user_edit_' . $id, $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $cible->setNom($request->request->get('nom'));
            $cible->setPrenom($request->request->get('prenom'));
            $cible->setEmail($request->request->get('email'));

            $profil = $this->profilRepo->find($request->request->getInt('profil_id'));
            if ($profil) { $cible->setProfil($profil); }

            $pwd = $request->request->get('password');
            if ($pwd) { $cible->setMotDePasse(password_hash($pwd, PASSWORD_BCRYPT)); }

            $this->em->flush();
            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/edit.html.twig', array_merge($context, [
            'cible'   => $cible,
            'profils' => $this->profilRepo->findBy([], ['nom' => 'ASC']),
        ]));
    }

    #[Route('/{id}/supprimer', name: 'utilisateur_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);

        $cible = $this->utilisateurRepo->find($id);
        if (!$cible) { throw $this->createNotFoundException(); }
        if ($cible->getId() === $user->getId()) { $this->addFlash('error', 'Impossible de supprimer votre propre compte.'); return $this->redirectToRoute('utilisateur_index'); }

        if (!$this->isCsrfTokenValid('user_delete_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->em->remove($cible);
        $this->em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('utilisateur_index');
    }
}
