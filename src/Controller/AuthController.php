<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request, UtilisateurRepository $repo): Response
    {
        if ($request->getSession()->get('user_id')) {
            return $this->redirectToRoute('dashboard');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email', '');
            $mdp   = $request->request->get('mot_de_passe', '');

            $utilisateur = $repo->findOneBy(['email' => $email]);

            if ($utilisateur && password_verify($mdp, $utilisateur->getMotDePasse())) {
                $request->getSession()->set('user_id', $utilisateur->getId());
                $request->getSession()->set('user_nom', $utilisateur->getNomComplet());
                $request->getSession()->set('user_profil', $utilisateur->getProfil()->getNom());
                return $this->redirectToRoute('dashboard');
            }

            $error = 'Email ou mot de passe incorrect.';
        }

        return $this->render('auth/login.html.twig', ['error' => $error]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('login');
    }
}
