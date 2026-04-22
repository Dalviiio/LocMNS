<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\TypeDocument;
use App\Repository\CategorieRepository;
use App\Repository\DocumentRepository;
use App\Repository\MaterielRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/documents')]
class DocumentController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private DocumentRepository    $documentRepo,
        private MaterielRepository    $materielRepo,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'document_index')]
    public function index(Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);

        return $this->render('document/index.html.twig', array_merge($context, [
            'documents' => $this->documentRepo->findBy([], ['createdAt' => 'DESC']),
            'types'     => TypeDocument::cases(),
            'materiels' => $this->materielRepo->findBy([], ['nom' => 'ASC']),
        ]));
    }

    #[Route('/nouveau', name: 'document_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);

        if (!$this->isCsrfTokenValid('document_new', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $materiel = $this->materielRepo->find($request->request->getInt('materiel_id'));
        if (!$materiel) { $this->addFlash('error', 'Matériel invalide.'); return $this->redirectToRoute('document_index'); }

        $doc = new Document();
        $doc->setMateriel($materiel);
        $doc->setType(TypeDocument::from($request->request->get('type')));
        $doc->setTitre($request->request->get('titre'));
        $doc->setUrl($request->request->get('url'));
        $this->em->persist($doc);
        $this->em->flush();

        $this->addFlash('success', 'Document ajouté.');
        return $this->redirectToRoute('document_index');
    }

    #[Route('/{id}/supprimer', name: 'document_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $doc = $this->documentRepo->find($id);
        if (!$doc) { throw $this->createNotFoundException(); }

        if (!$this->isCsrfTokenValid('doc_delete_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->em->remove($doc);
        $this->em->flush();

        $this->addFlash('success', 'Document supprimé.');
        return $this->redirectToRoute('document_index');
    }
}
