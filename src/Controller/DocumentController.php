<?php

namespace App\Controller;

use App\Repository\DocumentRepository;
use App\Repository\MaterielRepository;
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
        return $this->render('document/index.html.twig', [
            'documents' => $repo->findWithFilters(
                $request->query->get('search'),
                $request->query->get('type'),
            ),
            'materiels' => $materielRepo->findAll(),
        ]);
    }
}
