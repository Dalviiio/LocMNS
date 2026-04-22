<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractAppController
{
    public function __construct(UtilisateurRepository $utilisateurRepo)
    {
        parent::__construct($utilisateurRepo);
    }

    #[Route('/login', name: 'login', methods: ['GET'])]
    public function loginForm(): Response
    {
        return $this->render('auth/login.html.twig');
    }

    #[Route('/login', name: 'login_check', methods: ['POST'])]
    public function loginCheck(Request $request): Response
    {
        $email    = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');

        $user = $this->utilisateurRepo->findByEmail($email);

        if ($user && password_verify($password, $user->getMotDePasse())) {
            $session = $request->getSession();
            $session->set('user_id',     $user->getId());
            $session->set('user_nom',    $user->getNom());
            $session->set('user_prenom', $user->getPrenom());
            $session->set('user_profil', $user->getProfil()->getNom());

            return $this->redirectToRoute('dashboard');
        }

        $this->addFlash('error', 'Email ou mot de passe incorrect.');
        return $this->redirectToRoute('login');
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->invalidate();
        return $this->redirectToRoute('login');
    }
}
