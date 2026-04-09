<?php

namespace App\Controller;

use App\Repository\EmpruntRepository;
use App\Repository\MaterielRepository;
use App\Service\AutorisationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/export', name: 'export_')]
class ExportController extends AbstractController
{
    #[Route('/materiels/csv', name: 'materiels_csv')]
    public function materielsCsv(MaterielRepository $repo, AutorisationService $auth): StreamedResponse
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        $materiels = $repo->findAll();

        $response = new StreamedResponse(function () use ($materiels) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, ['ID', 'Nom', 'N° Série', 'Catégorie', 'État', 'Localisation', 'Date ajout'], ';');

            foreach ($materiels as $m) {
                fputcsv($handle, [
                    $m->getId(),
                    $m->getNom(),
                    $m->getNumeroSerie() ?? '',
                    $m->getCategorie()->getNom(),
                    $m->getEtat()->label(),
                    $m->getLocalisation() ?? '',
                    $m->getCreatedAt()->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="materiels_' . date('Ymd') . '.csv"');

        return $response;
    }

    #[Route('/emprunts/csv', name: 'emprunts_csv')]
    public function empruntsCsv(EmpruntRepository $repo, AutorisationService $auth): StreamedResponse
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        $emprunts = $repo->findAll();

        $response = new StreamedResponse(function () use ($emprunts) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Utilisateur', 'Matériel', 'Date début', 'Date fin prévue', 'Date retour', 'Statut'], ';');

            foreach ($emprunts as $e) {
                fputcsv($handle, [
                    $e->getId(),
                    $e->getUtilisateur()->getNomComplet(),
                    $e->getMateriel()->getNom(),
                    $e->getDateDebut()->format('d/m/Y H:i'),
                    $e->getDateFinPrevue()->format('d/m/Y H:i'),
                    $e->getDateRetour()?->format('d/m/Y H:i') ?? '',
                    $e->getStatut()->label(),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="emprunts_' . date('Ymd') . '.csv"');

        return $response;
    }

    #[Route('/materiels/xml', name: 'materiels_xml')]
    public function materielsXml(MaterielRepository $repo, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire()) {
            throw $this->createAccessDeniedException();
        }
        $materiels = $repo->findAll();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><materiels/>');

        foreach ($materiels as $m) {
            $node = $xml->addChild('materiel');
            $node->addChild('id', $m->getId());
            $node->addChild('nom', $m->getNom());
            $node->addChild('numero_serie', $m->getNumeroSerie() ?? '');
            $node->addChild('categorie', $m->getCategorie()->getNom());
            $node->addChild('etat', $m->getEtat()->label());
            $node->addChild('localisation', $m->getLocalisation() ?? '');
            $node->addChild('date_ajout', $m->getCreatedAt()->format('Y-m-d'));
        }

        return new Response(
            $xml->asXML(),
            200,
            [
                'Content-Type'        => 'application/xml; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="materiels_' . date('Ymd') . '.xml"',
            ]
        );
    }
}
