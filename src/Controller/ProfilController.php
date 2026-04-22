<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProfilRepository;
use App\Repository\UtilisateurRepository;
use App\Service\RoleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profils')]
class ProfilController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private ProfilRepository      $profilRepo,
        private CategorieRepository   $categorieRepo,
        private RoleService           $roleService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'profil_index')]
    public function index(Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);

        return $this->render('profil/index.html.twig', array_merge(
            $this->getProfilContext($request),
            [
                'profils'    => $this->profilRepo->findBy([], ['nom' => 'ASC']),
                'categories' => $this->categorieRepo->findBy([], ['nom' => 'ASC']),
            ]
        ));
    }

    #[Route('/{id}/categories', name: 'profil_categories', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateCategories(int $id, Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);
        $this->roleService->verifier($user, ['Administrateur']);

        $profil = $this->profilRepo->find($id);
        if (!$profil) { throw $this->createNotFoundException(); }

        if (!$this->isCsrfTokenValid('profil_cat_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        foreach ($profil->getCategories() as $c) { $profil->removeCategorie($c); }

        foreach ($request->request->all('categories') as $catId) {
            $cat = $this->categorieRepo->find((int) $catId);
            if ($cat) { $profil->addCategorie($cat); }
        }

        $this->em->flush();
        $this->addFlash('success', 'Catégories du profil mises à jour.');
        return $this->redirectToRoute('profil_index');
    }
}
