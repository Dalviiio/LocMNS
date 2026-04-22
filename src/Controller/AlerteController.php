<?php

namespace App\Controller;

use App\Repository\AlerteRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/alertes')]
class AlerteController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private AlerteRepository      $alerteRepo,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'alerte_index')]
    public function index(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $search = $request->query->get('search');
        $type   = $request->query->get('type');
        $userId = $context['isGestionnaire'] ? null : $user->getId();

        return $this->render('alerte/index.html.twig', array_merge($context, [
            'alertes' => $this->alerteRepo->findWithFilters($search, $type, $userId),
            'search'  => $search,
            'typeFiltre' => $type,
        ]));
    }

    #[Route('/{id}/lire', name: 'alerte_lire', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function marquerLue(int $id, Request $request): Response
    {
        $user  = $this->getUtilisateurConnecte($request);
        $alerte = $this->alerteRepo->find($id);

        if (!$alerte) { throw $this->createNotFoundException(); }

        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire'] && $alerte->getUtilisateur()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('alerte_lire_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $alerte->setLu(true);
        $this->em->flush();

        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/tout-lire', name: 'alerte_tout_lire', methods: ['POST'])]
    public function toutLire(Request $request): Response
    {
        $user  = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        if (!$this->isCsrfTokenValid('alerte_tout_lire', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $userId = $context['isGestionnaire'] ? null : $user->getId();
        $alertes = $this->alerteRepo->findNonLues($userId);
        foreach ($alertes as $a) { $a->setLu(true); }

        $this->em->flush();
        $this->addFlash('success', 'Toutes les alertes ont été marquées comme lues.');
        return $this->redirectToRoute('alerte_index');
    }
}
