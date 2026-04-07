<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProfilRepository;
use App\Service\AutorisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profils', name: 'profil_')]
class ProfilController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ProfilRepository $profilRepo, CategorieRepository $catRepo, AutorisationService $auth): Response
    {
        if (!$auth->isAdmin()) {
            throw $this->createAccessDeniedException('Réservé aux administrateurs.');
        }

        return $this->render('profil/index.html.twig', [
            'profils'    => $profilRepo->findAll(),
            'categories' => $catRepo->findAll(),
        ]);
    }

    #[Route('/autorisations', name: 'autorisations', methods: ['POST'])]
    public function saveAutorisations(
        Request $request,
        ProfilRepository $profilRepo,
        CategorieRepository $catRepo,
        EntityManagerInterface $em,
        AutorisationService $auth,
    ): Response {
        if (!$auth->isAdmin()) {
            throw $this->createAccessDeniedException('Réservé aux administrateurs.');
        }
        if (!$this->isCsrfTokenValid('save_autorisations', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $profils    = $profilRepo->findAll();
        $categories = $catRepo->findAll();
        $checked    = $request->request->all('autorisations'); // ['profil_id' => ['cat_id' => '1', ...]]

        foreach ($profils as $profil) {
            // Retirer toutes les catégories actuelles
            foreach ($profil->getCategories()->toArray() as $cat) {
                $profil->removeCategorie($cat);
            }
            // Remettre celles cochées
            $profilChecked = $checked[$profil->getId()] ?? [];
            foreach ($categories as $cat) {
                if (isset($profilChecked[$cat->getId()])) {
                    $profil->addCategorie($cat);
                }
            }
        }

        $em->flush();
        $this->addFlash('success', 'Autorisations mises à jour.');
        return $this->redirectToRoute('profil_index');
    }
}
