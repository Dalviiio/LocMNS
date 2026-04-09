<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\TypeAlerte;
use App\Repository\AlerteRepository;
use App\Service\AutorisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alertes', name: 'alerte_')]
class AlerteController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, AlerteRepository $repo, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');

        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $type   = isset($raw['type'])   ? (string) $raw['type']   : '';

        return $this->render('alerte/index.html.twig', [
            'alertes'       => $repo->findWithFilters($search ?: null, $type ?: null),
            'types'         => TypeAlerte::cases(),
            'count_nonlues' => $repo->countNonLues(),
            'filtre_search' => $search,
            'filtre_type'   => $type,
        ]);
    }

    #[Route('/api/dernieres', name: 'api_dernieres', methods: ['GET'])]
    public function apiDernieres(AlerteRepository $repo, AutorisationService $auth): JsonResponse
    {
        if (!$auth->isAdminOrGestionnaire()) {
            return new JsonResponse(['alertes' => [], 'total' => 0]);
        }

        $alertes = array_slice($repo->findNonLues(), 0, 5);
        $data = array_map(fn(Alerte $a) => [
            'id'      => $a->getId(),
            'type'    => $a->getType()->label(),
            'badge'   => $a->getType()->badgeClass(),
            'message' => $a->getMessage(),
            'user'    => $a->getUtilisateur()->getNomComplet(),
            'date'    => $a->getCreatedAt()->format('d/m/Y H:i'),
        ], $alertes);

        return new JsonResponse(['alertes' => $data, 'total' => $repo->countNonLues()]);
    }

    #[Route('/{id}/lire', name: 'lire', methods: ['POST'])]
    public function marquerLu(Request $request, Alerte $alerte, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        if (!$auth->isAdminOrGestionnaire() && $alerte->getUtilisateur()->getId() !== $auth->getUserId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette alerte.');
        }
        if ($this->isCsrfTokenValid('lire_alerte' . $alerte->getId(), $request->request->get('_token'))) {
            $alerte->setLu(true);
            $em->flush();
        }
        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/tout-lire', name: 'tout_lire', methods: ['POST'])]
    public function marquerToutLu(Request $request, AlerteRepository $repo, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($this->isCsrfTokenValid('tout_lire', $request->request->get('_token'))) {
            foreach ($repo->findNonLues() as $alerte) {
                $alerte->setLu(true);
            }
            $em->flush();
            $this->addFlash('success', 'Toutes les alertes ont été marquées comme lues.');
        }
        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Alerte $alerte, EntityManagerInterface $em, AutorisationService $auth): Response
    {
        $auth->verifier(['Administrateur', 'Gestionnaire'], 'Accès réservé aux gestionnaires.');
        if ($this->isCsrfTokenValid('del_alerte' . $alerte->getId(), $request->request->get('_token'))) {
            $em->remove($alerte);
            $em->flush();
        }
        return $this->redirectToRoute('alerte_index');
    }
}
