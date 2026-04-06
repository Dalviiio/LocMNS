<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories', name: 'categorie_')]
class CategorieController extends AbstractController
{
    #[Route('/nouvelle', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('new_categorie', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $nom = trim($request->request->get('nom', ''));
        if ($nom) {
            $categorie = new Categorie();
            $categorie->setNom($nom);
            $categorie->setDescription($request->request->get('description'));
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie "' . $nom . '" créée.');
        }

        return $this->redirectToRoute('materiel_index');
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('del_cat' . $categorie->getId(), $request->request->get('_token'))) {
            $em->remove($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée.');
        }
        return $this->redirectToRoute('materiel_index');
    }
}
