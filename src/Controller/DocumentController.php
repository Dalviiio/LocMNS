<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\TypeDocument;
use App\Repository\DocumentRepository;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/documents', name: 'document_')]
class DocumentController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, DocumentRepository $repo, MaterielRepository $materielRepo): Response
    {
        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $type   = isset($raw['type'])   ? (string) $raw['type']   : '';

        return $this->render('document/index.html.twig', [
            'documents'     => $repo->findWithFilters($search ?: null, $type ?: null),
            'materiels'     => $materielRepo->findAll(),
            'types'         => TypeDocument::cases(),
            'filtre_search' => $search,
            'filtre_type'   => $type,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, MaterielRepository $materielRepo): Response
    {
        if (!$this->isCsrfTokenValid('new_document', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $materiel = $materielRepo->find($request->request->getInt('materiel_id'));
        if ($materiel) {
            $doc = new Document();
            $doc->setMateriel($materiel);
            $doc->setTitre($request->request->get('titre'));
            $doc->setUrl($request->request->get('url'));
            $doc->setType(TypeDocument::from($request->request->get('type')));
            $em->persist($doc);
            $em->flush();
            $this->addFlash('success', 'Document ajouté.');
        }

        return $this->redirectToRoute('document_index');
    }
}
