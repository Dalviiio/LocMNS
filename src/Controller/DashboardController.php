<?php

namespace App\Controller;

use App\Repository\AlerteRepository;
use App\Repository\EmpruntRepository;
use App\Repository\EvenementRepository;
use App\Repository\MaterielRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        MaterielRepository   $materielRepo,
        EmpruntRepository    $empruntRepo,
        ReservationRepository $reservationRepo,
        EvenementRepository  $evenementRepo,
        AlerteRepository     $alerteRepo,
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'kpi' => [
                'materiels_indisponibles' => $materielRepo->countIndisponibles(),
                'retards'                 => $empruntRepo->countRetards(),
                'incidents'               => $evenementRepo->countIncidentsOuverts(),
                'demandes'                => $reservationRepo->countEnAttente(),
            ],
            'stock' => [
                'total'      => $materielRepo->countTotal(),
                'disponibles' => $materielRepo->countDisponibles(),
                'empruntes'  => $materielRepo->countEmpruntes(),
            ],
            'alertes_recentes'  => $alerteRepo->findNonLues(),
            'emprunts_en_cours' => $empruntRepo->findEnCours(),
            'reservations_mois' => $reservationRepo->findPlanning(
                new \DateTime('first day of this month 00:00:00'),
                new \DateTime('last day of this month 23:59:59'),
            ),
            'cal_annee'  => (int) (new \DateTime())->format('Y'),
            'cal_mois'   => (int) (new \DateTime())->format('n'),
        ]);
    }
}
