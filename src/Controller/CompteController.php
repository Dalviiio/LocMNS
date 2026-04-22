<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/compte')]
class CompteController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'compte_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('compte_update', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));

            $pwd = $request->request->get('password');
            if ($pwd) {
                $user->setMotDePasse(password_hash($pwd, PASSWORD_BCRYPT));
            }

            $this->em->flush();

            $session = $request->getSession();
            $session->set('user_nom', $user->getNom());
            $session->set('user_prenom', $user->getPrenom());

            $this->addFlash('success', 'Votre profil a été mis à jour.');
            return $this->redirectToRoute('compte_index');
        }

        return $this->render('compte/index.html.twig', array_merge($context, [
            'user' => $user,
        ]));
    }
}
