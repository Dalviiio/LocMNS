<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\EtatMateriel;
use App\Entity\Materiel;
use App\Entity\TypeDocument;
use App\Repository\CategorieRepository;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/materiels', name: 'materiel_')]
class MaterielController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, MaterielRepository $repo, CategorieRepository $catRepo): Response
    {
        $search      = $request->query->get('search');
        $etat        = $request->query->get('etat');
        $categorieId = $request->query->getInt('categorie') ?: null;

        return $this->render('materiel/index.html.twig', [
            'materiels'  => $repo->findWithFilters($search, $etat, $categorieId),
            'categories' => $catRepo->findAll(),
            'etats'      => EtatMateriel::cases(),
            'search'     => $search,
            'etat_filtre' => $etat,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $catRepo): Response
    {
        if ($request->isMethod('POST')) {
            $materiel = new Materiel();
            $this->hydrateFromRequest($materiel, $request, $catRepo);
            $em->persist($materiel);
            $em->flush();
            $this->addFlash('success', 'Matériel ajouté avec succès.');
            return $this->redirectToRoute('materiel_index');
        }

        return $this->render('materiel/form.html.twig', [
            'materiel'   => null,
            'categories' => $catRepo->findAll(),
            'etats'      => EtatMateriel::cases(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Materiel $materiel): Response
    {
        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $em, CategorieRepository $catRepo): Response
    {
        if ($request->isMethod('POST')) {
            $this->hydrateFromRequest($materiel, $request, $catRepo);
            $em->flush();
            $this->addFlash('success', 'Matériel modifié avec succès.');
            return $this->redirectToRoute('materiel_show', ['id' => $materiel->getId()]);
        }

        return $this->render('materiel/form.html.twig', [
            'materiel'   => $materiel,
            'categories' => $catRepo->findAll(),
            'etats'      => EtatMateriel::cases(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $materiel->getId(), $request->request->get('_token'))) {
            $em->remove($materiel);
            $em->flush();
            $this->addFlash('success', 'Matériel supprimé.');
        }
        return $this->redirectToRoute('materiel_index');
    }

    #[Route('/{id}/document/ajouter', name: 'document_add', methods: ['POST'])]
    public function addDocument(Request $request, Materiel $materiel, EntityManagerInterface $em): Response
    {
        $doc = new Document();
        $doc->setMateriel($materiel);
        $doc->setTitre($request->request->get('titre'));
        $doc->setUrl($request->request->get('url'));
        $doc->setType(TypeDocument::from($request->request->get('type')));
        $em->persist($doc);
        $em->flush();
        $this->addFlash('success', 'Document ajouté.');
        return $this->redirectToRoute('materiel_show', ['id' => $materiel->getId()]);
    }

    #[Route('/document/{id}/supprimer', name: 'document_delete', methods: ['POST'])]
    public function deleteDocument(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        $materielId = $document->getMateriel()->getId();
        if ($this->isCsrfTokenValid('del_doc' . $document->getId(), $request->request->get('_token'))) {
            $em->remove($document);
            $em->flush();
            $this->addFlash('success', 'Document supprimé.');
        }
        return $this->redirectToRoute('materiel_show', ['id' => $materielId]);
    }

    private function hydrateFromRequest(Materiel $materiel, Request $request, CategorieRepository $catRepo): void
    {
        $materiel->setNom($request->request->get('nom'));
        $materiel->setNumeroSerie($request->request->get('numero_serie') ?: null);
        $materiel->setLocalisation($request->request->get('localisation') ?: null);
        $materiel->setEtat(EtatMateriel::from($request->request->get('etat')));
        $categorie = $catRepo->find($request->request->getInt('categorie_id'));
        if ($categorie) {
            $materiel->setCategorie($categorie);
        }
    }
}
