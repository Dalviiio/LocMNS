<?php

namespace App\Controller;

use App\Repository\AlerteRepository;
use App\Repository\EmpruntRepository;
use App\Repository\EvenementRepository;
use App\Repository\MaterielRepository;
use App\Repository\ReservationRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private MaterielRepository   $materielRepo,
        private EmpruntRepository    $empruntRepo,
        private AlerteRepository     $alerteRepo,
        private ReservationRepository $reservationRepo,
        private EvenementRepository  $evenementRepo,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('/', name: 'dashboard')]
    public function index(Request $request): Response
    {
        $user = $this->getUtilisateurConnecte($request);

        $nbAlertes = $this->alerteRepo->countNonLues($user->getId());
        $request->getSession()->set('nb_alertes', $nbAlertes);

        return $this->render('dashboard/index.html.twig', array_merge(
            $this->getProfilContext($request),
            [
                'totalMateriels'    => $this->materielRepo->countTotal(),
                'materielsDispos'   => $this->materielRepo->countDisponibles(),
                'materielsEmpruntes'=> $this->materielRepo->countEmpruntes(),
                'retards'           => $this->empruntRepo->countRetards(),
                'reservationsEnAttente' => $this->reservationRepo->countEnAttente(),
                'incidents'         => $this->evenementRepo->countIncidentsOuverts(),
                'empruntsEnCours'   => $this->empruntRepo->findEnCours(),
                'planning'          => $this->empruntRepo->findPlanningDashboard(),
                'alertesRecentes'   => $this->alerteRepo->findNonLues($user->getId()),
            ]
        ));
    }
}
