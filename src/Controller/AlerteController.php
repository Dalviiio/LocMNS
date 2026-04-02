<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\TypeAlerte;
use App\Repository\AlerteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alertes', name: 'alerte_')]
class AlerteController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, AlerteRepository $repo): Response
    {
        return $this->render('alerte/index.html.twig', [
            'alertes'      => $repo->findWithFilters(
                $request->query->get('search'),
                $request->query->get('type'),
            ),
            'types'        => TypeAlerte::cases(),
            'count_nonlues' => $repo->countNonLues(),
        ]);
    }

    #[Route('/{id}/lire', name: 'lire', methods: ['POST'])]
    public function marquerLu(Alerte $alerte, EntityManagerInterface $em): Response
    {
        $alerte->setLu(true);
        $em->flush();
        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/tout-lire', name: 'tout_lire', methods: ['POST'])]
    public function marquerToutLu(AlerteRepository $repo, EntityManagerInterface $em): Response
    {
        foreach ($repo->findNonLues() as $alerte) {
            $alerte->setLu(true);
        }
        $em->flush();
        $this->addFlash('success', 'Toutes les alertes ont été marquées comme lues.');
        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Alerte $alerte, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('del_alerte' . $alerte->getId(), $request->request->get('_token'))) {
            $em->remove($alerte);
            $em->flush();
        }
        return $this->redirectToRoute('alerte_index');
    }
}
