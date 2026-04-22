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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservations')]
class ReservationController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private ReservationRepository $reservationRepo,
        private MaterielRepository    $materielRepo,
        private AutorisationService   $autorisationService,
        private AlerteService         $alerteService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'reservation_index')]
    public function index(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $search = $request->query->get('search');
        $statut = $request->query->get('statut');
        $userId = $context['isGestionnaire'] ? null : $user->getId();

        return $this->render('reservation/index.html.twig', array_merge($context, [
            'reservations' => $this->reservationRepo->findWithFilters($search, $statut, $userId),
            'statuts'      => StatutReservation::cases(),
            'search'       => $search,
            'statutFiltre' => $statut,
        ]));
    }

    #[Route('/nouveau', name: 'reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $categorieIds = [];
        if (!$context['isGestionnaire']) {
            foreach ($this->autorisationService->getCategoriesAutorisees($user) as $cat) {
                $categorieIds[] = $cat->getId();
            }
        }
        $materiels = $this->materielRepo->findWithFilters(null, null, null, $categorieIds);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reservation_new', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $materiel = $this->materielRepo->find($request->request->getInt('materiel_id'));
            if (!$materiel) { $this->addFlash('error', 'Matériel invalide.'); return $this->redirectToRoute('reservation_new'); }

            $this->autorisationService->verifierOuRefuser($user, $materiel);

            $reservation = new Reservation();
            $reservation->setUtilisateur($user);
            $reservation->setMateriel($materiel);
            $reservation->setDateDebut(new \DateTime($request->request->get('date_debut')));
            $reservation->setDateFin(new \DateTime($request->request->get('date_fin')));
            $reservation->setStatut(StatutReservation::EnAttente);
            $this->em->persist($reservation);

            foreach ($this->utilisateurRepo->findAll() as $u) {
                if (in_array($u->getProfil()->getNom(), ['Administrateur', 'Gestionnaire'], true)) {
                    $this->alerteService->creer($u, TypeAlerte::NouvelleDemande,
                        $user->getNomComplet() . ' demande une réservation : ' . $materiel->getNom());
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Réservation créée.');
            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/new.html.twig', array_merge($context, [
            'materiels' => $materiels,
        ]));
    }

    #[Route('/{id}/statut', name: 'reservation_statut', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function changerStatut(int $id, Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $reservation = $this->reservationRepo->find($id);
        if (!$reservation) { throw $this->createNotFoundException(); }

        if (!$this->isCsrfTokenValid('res_statut_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $reservation->setStatut(StatutReservation::from($request->request->get('statut')));
        $this->em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/planning', name: 'reservation_planning')]
    public function planning(Request $request): Response
    {
        $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $mois  = (int) $request->query->get('mois', date('n'));
        $annee = (int) $request->query->get('annee', date('Y'));

        return $this->render('reservation/planning.html.twig', array_merge($context, [
            'reservations' => $this->reservationRepo->findPlanning($mois, $annee),
            'mois'         => $mois,
            'annee'        => $annee,
            'moisNom'      => \IntlDateFormatter::create('fr_FR', 0, 0, null, null, 'MMMM yyyy')
                                ->format(mktime(0, 0, 0, $mois, 1, $annee)),
        ]));
    }
}
