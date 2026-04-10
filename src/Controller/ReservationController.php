<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\StatutReservation;
use App\Entity\TypeAlerte;
use App\Repository\MaterielRepository;
use App\Repository\ReservationRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AlerteService;
use App\Service\AutorisationService;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservations', name: 'reservation_')]
class ReservationController extends AbstractController
{
    // ──────────────────────────────────────────────────────────
    // LISTE
    // ──────────────────────────────────────────────────────────
    #[Route('', name: 'index')]
    public function index(Request $request, ReservationRepository $repo, AutorisationService $auth): Response
    {
        $isManager = $auth->isAdminOrGestionnaire();
        $uid       = $isManager ? null : $auth->getUserId();

        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $statut = isset($raw['statut']) ? (string) $raw['statut'] : '';

        $total     = $repo->countWithFilters($search ?: null, $statut ?: null, $uid);
        $paginator = Paginator::fromRequest($request, $total);

        return $this->render('reservation/index.html.twig', [
            'reservations'  => $repo->findWithFilters($search ?: null, $statut ?: null, $uid, $paginator->perPage, $paginator->offset),
            'statuts'       => StatutReservation::cases(),
            'filtre_search' => $search,
            'filtre_statut' => $statut,
            'is_manager'    => $isManager,
            'paginator'     => $paginator,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // PLANNING
    // ──────────────────────────────────────────────────────────
    #[Route('/planning', name: 'planning')]
    public function planning(Request $request, ReservationRepository $repo, AutorisationService $auth): Response
    {
        $isManager = $auth->isAdminOrGestionnaire();
        $uid       = $isManager ? null : $auth->getUserId();

        $raw   = $request->query->all();
        $annee = isset($raw['annee']) && ctype_digit($raw['annee']) ? (int) $raw['annee'] : (int) date('Y');
        $mois  = isset($raw['mois'])  && ctype_digit($raw['mois'])  ? (int) $raw['mois']  : (int) date('n');
        $mois  = max(1, min(12, $mois));

        $debut = new \DateTime(sprintf('%04d-%02d-01', $annee, $mois));
        $fin   = (clone $debut)->modify('last day of this month')->setTime(23, 59, 59);

        $prevDate = (clone $debut)->modify('-1 month');
        $nextDate = (clone $debut)->modify('+1 month');

        return $this->render('reservation/planning.html.twig', [
            'reservations' => $repo->findPlanning($debut, $fin, $uid),
            'debut'        => $debut,
            'fin'          => $fin,
            'prev'         => $prevDate,
            'next'         => $nextDate,
            'nb_jours'     => (int) $fin->format('d'),
            'premier_jour' => (int) $debut->format('N'),
            'is_manager'   => $isManager,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // NOUVELLE RÉSERVATION
    // ──────────────────────────────────────────────────────────
    #[Route('/nouvelle', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        ReservationRepository $resaRepo,
        AutorisationService $auth,
        AlerteService $alerteService,
    ): Response {
        $isManager    = $auth->isAdminOrGestionnaire();
        $userConnecte = $utilisateurRepo->find($auth->getUserId());
        $categoriesAutorisees = $auth->getCategoriesAutorisees();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_resa', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $dateDebut   = \DateTime::createFromFormat('Y-m-d', $request->request->get('date_debut'))->setTime(0, 0, 0);
            $dateFin     = \DateTime::createFromFormat('Y-m-d', $request->request->get('date_fin'))->setTime(0, 0, 0);
            $today       = new \DateTime('today');
            $materielNom = trim($request->request->get('materiel_nom', ''));
            $quantite    = max(1, $request->request->getInt('quantite', 1));

            if ($dateDebut < $today) {
                $this->addFlash('error', 'La date de début ne peut pas être dans le passé.');
                return $this->redirectToRoute('reservation_new');
            }
            if ($dateFin <= $dateDebut) {
                $this->addFlash('error', 'La date de fin doit être après la date de début.');
                return $this->redirectToRoute('reservation_new');
            }
            if (!$materielNom) {
                $this->addFlash('error', 'Veuillez sélectionner un matériel.');
                return $this->redirectToRoute('reservation_new');
            }

            // Trouver les N unités disponibles sur la période
            $unites = $materielRepo->findDisponiblesParNomPourPeriode(
                $materielNom, $dateDebut, $dateFin, $categoriesAutorisees, $quantite
            );

            if (count($unites) < $quantite) {
                $dispo = count($unites);
                $this->addFlash('error', $dispo === 0
                    ? "Aucun exemplaire de « {$materielNom} » n'est disponible sur cette période."
                    : "Seulement {$dispo} exemplaire(s) disponible(s) pour « {$materielNom} » sur cette période."
                );
                return $this->redirectToRoute('reservation_new');
            }

            // Utilisateur : manager choisit, sinon connecté
            $utilisateur = $isManager
                ? $utilisateurRepo->find($request->request->getInt('utilisateur_id'))
                : $userConnecte;

            if (!$utilisateur) {
                $this->addFlash('error', 'Utilisateur introuvable.');
                return $this->redirectToRoute('reservation_new');
            }

            // Créer une réservation par unité
            foreach (array_slice($unites, 0, $quantite) as $materiel) {
                $reservation = new Reservation();
                $reservation->setUtilisateur($utilisateur);
                $reservation->setMateriel($materiel);
                $reservation->setDateDebut($dateDebut);
                $reservation->setDateFin($dateFin);
                $em->persist($reservation);
            }

            $alerteService->creer(
                $utilisateur,
                sprintf('Réservation de %dx %s par %s', $quantite, $materielNom, $utilisateur->getNomComplet()),
                TypeAlerte::NouvelleDemande,
            );

            $em->flush();
            $this->addFlash('success', sprintf('%d réservation(s) créée(s) avec succès.', $quantite));
            return $this->redirectToRoute('reservation_index');
        }

        // GET — catalogue groupé par modèle
        return $this->render('reservation/form.html.twig', [
            'reservation'  => null,
            'catalogue'    => $materielRepo->findGroupedPourReservation($categoriesAutorisees),
            'utilisateurs' => $isManager ? $utilisateurRepo->findAll() : [],
            'is_manager'   => $isManager,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // ÉDITION
    // ──────────────────────────────────────────────────────────
    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        ReservationRepository $resaRepo,
        AutorisationService $auth,
    ): Response {
        $isManager    = $auth->isAdminOrGestionnaire();
        $userConnecte = $utilisateurRepo->find($auth->getUserId());

        // Vérification propriétaire pour les non-managers
        if (!$isManager && $reservation->getUtilisateur()->getId() !== $auth->getUserId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres réservations.');
        }

        // Non-managers ne peuvent éditer que les réservations en attente
        if (!$isManager && $reservation->getStatut() !== StatutReservation::EnAttente) {
            $this->addFlash('error', 'Vous ne pouvez modifier qu\'une réservation en attente.');
            return $this->redirectToRoute('reservation_index');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_resa' . $reservation->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $dateDebut = \DateTime::createFromFormat('Y-m-d', $request->request->get('date_debut'))->setTime(0, 0, 0);
            $dateFin   = \DateTime::createFromFormat('Y-m-d', $request->request->get('date_fin'))->setTime(0, 0, 0);
            $today     = new \DateTime('today');

            if ($dateDebut < $today) {
                $this->addFlash('error', 'La date de début ne peut pas être dans le passé.');
                return $this->redirectToRoute('reservation_edit', ['id' => $reservation->getId()]);
            }
            if ($dateFin <= $dateDebut) {
                $this->addFlash('error', 'La date de fin doit être après la date de début.');
                return $this->redirectToRoute('reservation_edit', ['id' => $reservation->getId()]);
            }

            $materielId = $request->request->getInt('materiel_id');
            $materiel   = $materielRepo->find($materielId);

            if (!$materiel) {
                $this->addFlash('error', 'Matériel introuvable.');
                return $this->redirectToRoute('reservation_edit', ['id' => $reservation->getId()]);
            }

            // Vérification conflits (en excluant la réservation courante)
            $conflits = $resaRepo->findConflit($materiel->getId(), $dateDebut, $dateFin, $reservation->getId());
            if (!empty($conflits)) {
                $c = $conflits[0];
                $this->addFlash('error', sprintf(
                    'Ce matériel est déjà réservé du %s au %s.',
                    $c->getDateDebut()->format('d/m/Y'),
                    $c->getDateFin()->format('d/m/Y')
                ));
                return $this->redirectToRoute('reservation_edit', ['id' => $reservation->getId()]);
            }

            $reservation->setMateriel($materiel);
            $reservation->setDateDebut($dateDebut);
            $reservation->setDateFin($dateFin);

            // Seul un manager peut changer l'utilisateur
            if ($isManager) {
                $utilisateur = $utilisateurRepo->find($request->request->getInt('utilisateur_id'));
                if ($utilisateur) {
                    $reservation->setUtilisateur($utilisateur);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Réservation modifiée avec succès.');
            return $this->redirectToRoute('reservation_index');
        }

        // GET
        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('reservation/form.html.twig', [
            'reservation'  => $reservation,
            'materiels'    => $materiels,
            'utilisateurs' => $isManager ? $utilisateurRepo->findAll() : [],
            'is_manager'   => $isManager,
            'acces_limite' => false,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // CHANGER STATUT  (Admin / Gestionnaire uniquement)
    // Transitions autorisées selon le flux métier :
    //   en_attente → approuvee | refusee | annulee
    //   approuvee  → confirmee | refusee | annulee
    //   confirmee  → en_cours  | annulee
    //   en_cours   → terminee  | en_retard | en_litige
    //   en_retard  → terminee  | en_litige | annulee
    //   en_litige  → terminee  | annulee
    // ──────────────────────────────────────────────────────────
    #[Route('/{id}/statut/{cible}', name: 'changer_statut', methods: ['POST'])]
    public function changerStatut(
        Request $request,
        Reservation $reservation,
        string $cible,
        EntityManagerInterface $em,
        AutorisationService $auth,
    ): Response {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');

        if (!$this->isCsrfTokenValid('statut_resa' . $reservation->getId() . '_' . $cible, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $transitions = [
            'en_attente' => ['approuvee', 'refusee', 'annulee'],
            'approuvee'  => ['confirmee', 'refusee', 'annulee'],
            'confirmee'  => ['en_cours', 'annulee'],
            'en_cours'   => ['terminee', 'en_retard', 'en_litige'],
            'en_retard'  => ['terminee', 'en_litige', 'annulee'],
            'en_litige'  => ['terminee', 'annulee'],
        ];

        $current = $reservation->getStatut()->value;
        $autorisees = $transitions[$current] ?? [];

        if (!in_array($cible, $autorisees, true)) {
            $this->addFlash('error', 'Transition de statut non autorisée.');
            return $this->redirectToRoute('reservation_index');
        }

        $nouveauStatut = StatutReservation::from($cible);
        $reservation->setStatut($nouveauStatut);
        $em->flush();

        $this->addFlash('success', sprintf('Statut mis à jour : %s.', $nouveauStatut->label()));
        return $this->redirectToRoute('reservation_index');
    }
}
