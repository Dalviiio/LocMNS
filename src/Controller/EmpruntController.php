<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Evenement;
use App\Entity\StatutEmprunt;
use App\Entity\TypeAlerte;
use App\Entity\TypeEvenement;
use App\Repository\EmpruntRepository;
use App\Repository\MaterielRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AlerteService;
use App\Service\AutorisationService;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emprunts', name: 'emprunt_')]
class EmpruntController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, EmpruntRepository $repo, AutorisationService $auth): Response
    {
        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $statut = isset($raw['statut']) ? (string) $raw['statut'] : '';

        $utilisateurId = $auth->isAdminOrGestionnaire()
            ? null
            : $request->getSession()->get('user_id');

        $total     = $repo->countWithFilters($search ?: null, $statut ?: null, $utilisateurId);
        $paginator = Paginator::fromRequest($request, $total);

        return $this->render('emprunt/index.html.twig', [
            'emprunts'      => $repo->findWithFilters($search ?: null, $statut ?: null, $utilisateurId, $paginator->perPage, $paginator->offset),
            'statuts'       => StatutEmprunt::cases(),
            'filtre_search' => $search,
            'filtre_statut' => $statut,
            'paginator'     => $paginator,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        AutorisationService $auth,
        AlerteService $alerteService,
    ): Response {
        $isManager = $auth->isAdminOrGestionnaire();
        $categoriesAutorisees = $auth->getCategoriesAutorisees();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_emprunt', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            if ($isManager) {
                // Gestionnaire : flux classique 1 emprunt
                $emprunt = new Emprunt();
                $this->hydrateFromRequest($emprunt, $request, $materielRepo, $utilisateurRepo);
                if ($emprunt->getMateriel()) {
                    $auth->verifierAccesMateriel($emprunt->getMateriel());
                }
                $em->persist($emprunt);

                $raw = $request->request->all();
                $accessoireIds = isset($raw['accessoire_ids']) && is_array($raw['accessoire_ids']) ? $raw['accessoire_ids'] : [];
                foreach ($accessoireIds as $accId) {
                    $accMateriel = $materielRepo->find((int) $accId);
                    if ($accMateriel && $accMateriel->isDisponible()) {
                        $empAcc = new Emprunt();
                        $empAcc->setUtilisateur($emprunt->getUtilisateur());
                        $empAcc->setMateriel($accMateriel);
                        $empAcc->setDateDebut($emprunt->getDateDebut());
                        $empAcc->setDateFinPrevue($emprunt->getDateFinPrevue());
                        $empAcc->setParentEmprunt($emprunt);
                        $em->persist($empAcc);
                    }
                }

                $alerteService->creer(
                    $emprunt->getUtilisateur(),
                    'Nouvel emprunt créé pour ' . $emprunt->getUtilisateur()->getNomComplet(),
                    TypeAlerte::NouvelleDemande,
                    $emprunt,
                );
            } else {
                // Client : par nom de matériel + quantité
                $materielNom = trim($request->request->get('materiel_nom', ''));
                $quantite    = max(1, min(10, $request->request->getInt('quantite', 1)));
                $dateDebut   = new \DateTime($request->request->get('date_debut'));
                $dateFin     = new \DateTime($request->request->get('date_fin_prevue'));
                $utilisateur = $utilisateurRepo->find($auth->getUserId());

                $unites = $materielRepo->findDisponiblesParNom($materielNom, $categoriesAutorisees, $quantite);
                if (empty($unites)) {
                    $this->addFlash('error', 'Aucune unité disponible pour "' . $materielNom . '".');
                    return $this->redirectToRoute('emprunt_new');
                }

                $emprunt = null;
                foreach ($unites as $unite) {
                    $emp = new Emprunt();
                    $emp->setUtilisateur($utilisateur);
                    $emp->setMateriel($unite);
                    $emp->setDateDebut($dateDebut);
                    $emp->setDateFinPrevue($dateFin);
                    if ($emprunt === null) {
                        $emprunt = $emp;
                    } else {
                        $emp->setParentEmprunt($emprunt);
                    }
                    $em->persist($emp);
                }

                $alerteService->creer(
                    $utilisateur,
                    'Nouvel emprunt créé pour ' . $utilisateur->getNomComplet(),
                    TypeAlerte::NouvelleDemande,
                    $emprunt,
                );
            }

            try {
                $em->flush();
            } catch (\Exception $e) {
                // rollback + fermeture de l'EM pour éviter un état corrompu avant re-throw
                if ($em->getConnection()->isTransactionActive()) {
                    $em->getConnection()->rollBack();
                }
                $em->close();
                throw $e;
            }

            $this->addFlash('success', 'Emprunt créé avec succès.');
            return $this->redirectToRoute('emprunt_index');
        }

        if ($isManager) {
            $materiels = $categoriesAutorisees
                ? $materielRepo->findByCategories($categoriesAutorisees)
                : $materielRepo->findAll();
            $catalogueClient = null;
        } else {
            $materiels       = null;
            $catalogueClient = $materielRepo->findGroupedPourClient($categoriesAutorisees);
        }

        return $this->render('emprunt/form.html.twig', [
            'emprunt'          => null,
            'materiels'        => $materiels,
            'catalogue_client' => $catalogueClient,
            'utilisateurs'     => $isManager ? $utilisateurRepo->findAll() : [],
            'acces_limite'     => !$isManager && empty($catalogueClient),
            'is_manager'      => $isManager,
            'user_id_connecte' => $auth->getUserId(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Emprunt $emprunt, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire() && $emprunt->getUtilisateur()->getId() !== $auth->getUserId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez consulter que vos propres emprunts.');
        }

        return $this->render('emprunt/show.html.twig', [
            'emprunt'              => $emprunt,
            'types_evenement'      => TypeEvenement::cases(),
            'emprunts_accessoires' => $emprunt->getEmpruntsAccessoires(),
        ]);
    }

    #[Route('/{id}/retour', name: 'retour', methods: ['POST'])]
    public function retour(Request $request, Emprunt $emprunt, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($this->isCsrfTokenValid('retour_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
            $now = new \DateTime();
            $emprunt->setStatut(StatutEmprunt::Rendu);
            $emprunt->setDateRetour($now);

            // Retour automatique des accessoires associés
            $nbAccessoires = 0;
            foreach ($emprunt->getEmpruntsAccessoires() as $empAcc) {
                if ($empAcc->getStatut() === StatutEmprunt::EnCours) {
                    $empAcc->setStatut(StatutEmprunt::Rendu);
                    $empAcc->setDateRetour($now);
                    $nbAccessoires++;
                }
            }

            $em->flush();
            $msg = 'Retour enregistré.';
            if ($nbAccessoires > 0) {
                $msg .= ' ' . $nbAccessoires . ' accessoire(s) également retourné(s).';
            }
            $this->addFlash('success', $msg);
        }
        return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
    }

    #[Route('/{id}/evenement', name: 'evenement_add', methods: ['POST'])]
    public function addEvenement(Request $request, Emprunt $emprunt, EntityManagerInterface $em, AlerteService $alerteService, AutorisationService $auth): Response
    {
        if (!$this->isCsrfTokenValid('evenement_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        if (!$auth->isAdminOrGestionnaire() && $emprunt->getUtilisateur()->getId() !== $auth->getUserId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez signaler un événement que sur vos propres emprunts.');
        }
        $evenement = new Evenement();
        $evenement->setEmprunt($emprunt);
        $evenement->setType(TypeEvenement::from($request->request->get('type')));
        $evenement->setDescription($request->request->get('description'));
        $em->persist($evenement);

        if (in_array($evenement->getType(), [TypeEvenement::Panne, TypeEvenement::Dysfonctionnement])) {
            $alerteService->creer(
                $emprunt->getUtilisateur(),
                $evenement->getType()->label() . ' signalé sur ' . $emprunt->getMateriel()->getNom(),
                TypeAlerte::Panne,
                $emprunt,
            );
        }

        if ($evenement->getType() === TypeEvenement::Prolongation) {
            $nouvelleFin = $request->request->get('nouvelle_date_fin');
            if ($nouvelleFin) {
                $emprunt->setDateFinPrevue(new \DateTime($nouvelleFin));
            }
        }

        $em->flush();
        $this->addFlash('success', 'Événement enregistré.');
        return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Emprunt $emprunt,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        AutorisationService $auth,
    ): Response {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $this->hydrateFromRequest($emprunt, $request, $materielRepo, $utilisateurRepo);
            $em->flush();
            $this->addFlash('success', 'Emprunt modifié.');
            return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
        }

        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('emprunt/form.html.twig', [
            'emprunt'      => $emprunt,
            'materiels'    => $materiels,
            'utilisateurs' => $utilisateurRepo->findAll(),
            'acces_limite' => false,
        ]);
    }

    private function hydrateFromRequest(
        Emprunt $emprunt,
        Request $request,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        ?int $forcerUtilisateurId = null,
    ): void {
        $emprunt->setDateDebut(new \DateTime($request->request->get('date_debut')));
        $emprunt->setDateFinPrevue(new \DateTime($request->request->get('date_fin_prevue')));

        $materiel = $materielRepo->find($request->request->getInt('materiel_id'));
        if ($materiel) $emprunt->setMateriel($materiel);

        $utilisateurId = $forcerUtilisateurId ?? $request->request->getInt('utilisateur_id');
        $utilisateur = $utilisateurRepo->find($utilisateurId);
        if ($utilisateur) $emprunt->setUtilisateur($utilisateur);
    }
}
