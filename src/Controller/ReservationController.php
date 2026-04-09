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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservations', name: 'reservation_')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ReservationRepository $repo, AutorisationService $auth): Response
    {
        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $statut = isset($raw['statut']) ? (string) $raw['statut'] : '';

        $utilisateurId = $auth->isAdminOrGestionnaire()
            ? null
            : $request->getSession()->get('user_id');

        return $this->render('reservation/index.html.twig', [
            'reservations'  => $repo->findWithFilters($search ?: null, $statut ?: null, $utilisateurId),
            'statuts'       => StatutReservation::cases(),
            'filtre_search' => $search,
            'filtre_statut' => $statut,
        ]);
    }

    #[Route('/planning', name: 'planning')]
    public function planning(Request $request, ReservationRepository $repo): Response
    {
        $raw   = $request->query->all();
        $annee = isset($raw['annee']) && ctype_digit($raw['annee']) ? (int) $raw['annee'] : (int) date('Y');
        $mois  = isset($raw['mois'])  && ctype_digit($raw['mois'])  ? (int) $raw['mois']  : (int) date('n');
        $mois  = max(1, min(12, $mois));

        $debut = new \DateTime(sprintf('%04d-%02d-01', $annee, $mois));
        $fin   = (clone $debut)->modify('last day of this month')->setTime(23, 59, 59);

        $prevDate = (clone $debut)->modify('-1 month');
        $nextDate = (clone $debut)->modify('+1 month');

        $reservations = $repo->findPlanning($debut, $fin);

        return $this->render('reservation/planning.html.twig', [
            'reservations' => $reservations,
            'debut'        => $debut,
            'fin'          => $fin,
            'prev'         => $prevDate,
            'next'         => $nextDate,
            'nb_jours'     => (int) $fin->format('d'),
            'premier_jour' => (int) $debut->format('N'),
        ]);
    }

    #[Route('/nouvelle', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        AutorisationService $auth,
        AlerteService $alerteService,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_resa', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $reservation = new Reservation();
            $this->hydrateFromRequest($reservation, $request, $materielRepo, $utilisateurRepo);

            if ($reservation->getMateriel()) {
                $auth->verifierAccesMateriel($reservation->getMateriel());
            }

            $em->persist($reservation);

            $alerteService->creer(
                $reservation->getUtilisateur(),
                'Nouvelle réservation de ' . $reservation->getUtilisateur()->getNomComplet() . ' pour ' . $reservation->getMateriel()->getNom(),
                TypeAlerte::NouvelleDemande,
            );

            $em->flush();
            $this->addFlash('success', 'Réservation créée.');
            return $this->redirectToRoute('reservation_index');
        }

        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('reservation/form.html.twig', [
            'reservation'  => null,
            'materiels'    => $materiels,
            'utilisateurs' => $utilisateurRepo->findAll(),
            'acces_limite' => !empty($categoriesAutorisees) && count($materiels) === 0,
        ]);
    }

    #[Route('/{id}/confirmer', name: 'confirmer', methods: ['POST'])]
    public function confirmer(Request $request, Reservation $reservation, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($this->isCsrfTokenValid('confirmer_resa' . $reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatut(StatutReservation::Confirmee);
            $em->flush();
            $this->addFlash('success', 'Réservation confirmée.');
        }
        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['POST'])]
    public function annuler(Request $request, Reservation $reservation, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($this->isCsrfTokenValid('annuler_resa' . $reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatut(StatutReservation::Annulee);
            $em->flush();
            $this->addFlash('success', 'Réservation annulée.');
        }
        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        AutorisationService $auth,
    ): Response {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_resa' . $reservation->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $this->hydrateFromRequest($reservation, $request, $materielRepo, $utilisateurRepo);
            $em->flush();
            $this->addFlash('success', 'Réservation modifiée.');
            return $this->redirectToRoute('reservation_index');
        }

        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('reservation/form.html.twig', [
            'reservation'  => $reservation,
            'materiels'    => $materiels,
            'utilisateurs' => $utilisateurRepo->findAll(),
            'acces_limite' => false,
        ]);
    }

    private function hydrateFromRequest(
        Reservation $reservation,
        Request $request,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
    ): void {
        $reservation->setDateDebut(new \DateTime($request->request->get('date_debut')));
        $reservation->setDateFin(new \DateTime($request->request->get('date_fin')));

        $materiel = $materielRepo->find($request->request->getInt('materiel_id'));
        if ($materiel) $reservation->setMateriel($materiel);

        $utilisateur = $utilisateurRepo->find($request->request->getInt('utilisateur_id'));
        if ($utilisateur) $reservation->setUtilisateur($utilisateur);
    }
}
