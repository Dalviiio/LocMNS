<?php

namespace App\Controller;

use App\Repository\AlerteRepository;
use App\Repository\EmpruntRepository;
use App\Repository\EvenementRepository;
use App\Repository\MaterielRepository;
use App\Repository\ReservationRepository;
use App\Service\AutorisationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        MaterielRepository    $materielRepo,
        EmpruntRepository     $empruntRepo,
        ReservationRepository $reservationRepo,
        EvenementRepository   $evenementRepo,
        AlerteRepository      $alerteRepo,
        AutorisationService   $auth,
    ): Response {
        $isManager = $auth->isAdminOrGestionnaire();
        $uid = $isManager ? null : $auth->getUserId();

        $debut = new \DateTime('first day of this month 00:00:00');
        $fin   = new \DateTime('last day of this month 23:59:59');

        return $this->render('dashboard/index.html.twig', [
            'kpi' => [
                'materiels_indisponibles' => $isManager ? $materielRepo->countIndisponibles() : null,
                'retards'                 => $empruntRepo->countRetards($uid),
                'incidents'               => $isManager ? $evenementRepo->countIncidentsOuverts() : null,
                'demandes'                => $isManager ? $reservationRepo->countEnAttente() : $reservationRepo->countEnAttente($uid),
            ],
            'stock' => $isManager ? [
                'total'       => $materielRepo->countTotal(),
                'disponibles' => $materielRepo->countDisponibles(),
                'empruntes'   => $materielRepo->countEmpruntes(),
            ] : null,
            'alertes_recentes'  => $isManager ? $alerteRepo->findNonLues() : [],
            'emprunts_en_cours' => $empruntRepo->findEnCours($uid),
            'reservations_mois' => $reservationRepo->findPlanning($debut, $fin, $uid),
            'cal_annee'         => (int) (new \DateTime())->format('Y'),
            'cal_mois'          => (int) (new \DateTime())->format('n'),
            'is_manager'        => $isManager,
        ]);
    }
}
