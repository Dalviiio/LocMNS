<?php

namespace App\Controller;

use App\Entity\EtatMateriel;
use App\Entity\Materiel;
use App\Repository\CategorieRepository;
use App\Repository\MaterielRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AutorisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/materiels')]
class MaterielController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private MaterielRepository    $materielRepo,
        private CategorieRepository   $categorieRepo,
        private AutorisationService   $autorisationService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'materiel_index')]
    public function index(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $search      = $request->query->get('search');
        $etat        = $request->query->get('etat');
        $categorieId = $request->query->getInt('categorie') ?: null;

        $categorieIds = [];
        if (!$context['isGestionnaire']) {
            foreach ($this->autorisationService->getCategoriesAutorisees($user) as $cat) {
                $categorieIds[] = $cat->getId();
            }
        }

        $materiels  = $this->materielRepo->findWithFilters($search, $etat, $categorieId, $categorieIds);
        $categories = $this->categorieRepo->findBy([], ['nom' => 'ASC']);

        return $this->render('materiel/index.html.twig', array_merge($context, [
            'materiels'   => $materiels,
            'categories'  => $categories,
            'etats'       => EtatMateriel::cases(),
            'search'      => $search,
            'etatFiltre'  => $etat,
            'catFiltre'   => $categorieId,
        ]));
    }

    #[Route('/nouveau', name: 'materiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        if (!$context['isGestionnaire']) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('materiel_new', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $materiel = new Materiel();
            $materiel->setNom($request->request->get('nom'));
            $materiel->setNumeroSerie($request->request->get('numero_serie') ?: null);
            $materiel->setEtat(EtatMateriel::from($request->request->get('etat')));
            $materiel->setLocalisation($request->request->get('localisation') ?: null);

            $categorie = $this->categorieRepo->find($request->request->getInt('categorie_id'));
            if (!$categorie) {
                $this->addFlash('error', 'Catégorie invalide.');
                return $this->redirectToRoute('materiel_new');
            }
            $materiel->setCategorie($categorie);

            $this->em->persist($materiel);
            $this->em->flush();

            $this->addFlash('success', 'Matériel créé avec succès.');
            return $this->redirectToRoute('materiel_show', ['id' => $materiel->getId()]);
        }

        return $this->render('materiel/new.html.twig', array_merge($context, [
            'categories' => $this->categorieRepo->findBy([], ['nom' => 'ASC']),
            'etats'      => EtatMateriel::cases(),
        ]));
    }

    #[Route('/{id}', name: 'materiel_show', requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);
        $materiel = $this->materielRepo->find($id);

        if (!$materiel) { throw $this->createNotFoundException(); }

        if (!$context['isGestionnaire']) {
            $this->autorisationService->verifierOuRefuser($user, $materiel);
        }

        return $this->render('materiel/show.html.twig', array_merge($context, [
            'materiel' => $materiel,
        ]));
    }

    #[Route('/{id}/modifier', name: 'materiel_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $materiel = $this->materielRepo->find($id);
        if (!$materiel) { throw $this->createNotFoundException(); }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('materiel_edit_' . $id, $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $materiel->setNom($request->request->get('nom'));
            $materiel->setNumeroSerie($request->request->get('numero_serie') ?: null);
            $materiel->setEtat(EtatMateriel::from($request->request->get('etat')));
            $materiel->setLocalisation($request->request->get('localisation') ?: null);

            $categorie = $this->categorieRepo->find($request->request->getInt('categorie_id'));
            if ($categorie) { $materiel->setCategorie($categorie); }

            $this->em->flush();
            $this->addFlash('success', 'Matériel modifié avec succès.');
            return $this->redirectToRoute('materiel_show', ['id' => $materiel->getId()]);
        }

        return $this->render('materiel/edit.html.twig', array_merge($context, [
            'materiel'   => $materiel,
            'categories' => $this->categorieRepo->findBy([], ['nom' => 'ASC']),
            'etats'      => EtatMateriel::cases(),
        ]));
    }

    #[Route('/{id}/supprimer', name: 'materiel_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isAdmin']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $materiel = $this->materielRepo->find($id);
        if (!$materiel) { throw $this->createNotFoundException(); }

        if (!$this->isCsrfTokenValid('materiel_delete_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->em->remove($materiel);
        $this->em->flush();

        $this->addFlash('success', 'Matériel supprimé.');
        return $this->redirectToRoute('materiel_index');
    }

    #[Route('/{id}/accessoires', name: 'materiel_accessoires_json', requirements: ['id' => '\d+'])]
    public function accessoiresJson(int $id, Request $request): JsonResponse
    {
        $user     = $this->getUtilisateurConnecte($request);
        $materiel = $this->materielRepo->find($id);
        if (!$materiel) { return new JsonResponse([]); }

        $data = [];
        foreach ($materiel->getAccessoires() as $acc) {
            $emprunte = false;
            foreach ($acc->getEmprunts() as $emp) {
                if ($emp->getStatut()->value === 'EnCours') {
                    $emprunte = true;
                    break;
                }
            }
            $data[] = [
                'id'         => $acc->getId(),
                'nom'        => $acc->getNom(),
                'disponible' => !$emprunte,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/export', name: 'materiel_export')]
    public function export(Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $materiels = $this->materielRepo->findWithFilters();

        $csv = "ID;Nom;N° Série;État;Localisation;Catégorie;Créé le\n";
        foreach ($materiels as $m) {
            $csv .= implode(';', [
                $m->getId(),
                $m->getNom(),
                $m->getNumeroSerie() ?? '',
                $m->getEtat()->label(),
                $m->getLocalisation() ?? '',
                $m->getCategorie()->getNom(),
                $m->getCreatedAt()->format('d/m/Y'),
            ]) . "\n";
        }

        return new Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="materiels.csv"',
        ]);
    }
}
