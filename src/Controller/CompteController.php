<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte', name: 'compte_')]
class CompteController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, UtilisateurRepository $repo, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $utilisateur = $repo->find($userId);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_compte', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setEmail($request->request->get('email'));

            $mdp = $request->request->get('mot_de_passe');
            if ($mdp) {
                $utilisateur->setMotDePasse(password_hash($mdp, PASSWORD_BCRYPT));
            }

            $em->flush();

            $request->getSession()->set('user_nom', $utilisateur->getNomComplet());
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('compte_index');
        }

        return $this->render('compte/index.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
}
