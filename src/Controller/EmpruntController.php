<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\Emprunt;
use App\Entity\Evenement;
use App\Entity\StatutEmprunt;
use App\Entity\TypeAlerte;
use App\Entity\TypeEvenement;
use App\Repository\EmpruntRepository;
use App\Repository\MaterielRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emprunts', name: 'emprunt_')]
class EmpruntController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, EmpruntRepository $repo): Response
    {
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $repo->findWithFilters(
                $request->query->get('search'),
                $request->query->get('statut'),
            ),
            'statuts' => StatutEmprunt::cases(),
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_emprunt', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $emprunt = new Emprunt();
            $this->hydrateFromRequest($emprunt, $request, $materielRepo, $utilisateurRepo);
            $em->persist($emprunt);

            // Alerte nouvelle demande
            $alerte = new Alerte();
            $alerte->setUtilisateur($emprunt->getUtilisateur());
            $alerte->setEmprunt($emprunt);
            $alerte->setType(TypeAlerte::NouvelleDemande);
            $alerte->setMessage('Nouvel emprunt créé pour ' . $emprunt->getUtilisateur()->getNomComplet());
            $em->persist($alerte);

            $em->flush();
            $this->addFlash('success', 'Emprunt créé avec succès.');
            return $this->redirectToRoute('emprunt_index');
        }

        return $this->render('emprunt/form.html.twig', [
            'emprunt'      => null,
            'materiels'    => $materielRepo->findAll(),
            'utilisateurs' => $utilisateurRepo->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Emprunt $emprunt): Response
    {
        return $this->render('emprunt/show.html.twig', [
            'emprunt'          => $emprunt,
            'types_evenement'  => TypeEvenement::cases(),
        ]);
    }

    #[Route('/{id}/retour', name: 'retour', methods: ['POST'])]
    public function retour(Request $request, Emprunt $emprunt, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('retour_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
            $emprunt->setStatut(StatutEmprunt::Rendu);
            $emprunt->setDateRetour(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Retour enregistré.');
        }
        return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
    }

    #[Route('/{id}/evenement', name: 'evenement_add', methods: ['POST'])]
    public function addEvenement(Request $request, Emprunt $emprunt, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('evenement_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $evenement = new Evenement();
        $evenement->setEmprunt($emprunt);
        $evenement->setType(TypeEvenement::from($request->request->get('type')));
        $evenement->setDescription($request->request->get('description'));
        $em->persist($evenement);

        // Si panne → alerte
        if ($evenement->getType() === TypeEvenement::Panne || $evenement->getType() === TypeEvenement::Dysfonctionnement) {
            $alerte = new Alerte();
            $alerte->setUtilisateur($emprunt->getUtilisateur());
            $alerte->setEmprunt($emprunt);
            $alerte->setType(TypeAlerte::Panne);
            $alerte->setMessage($evenement->getType()->label() . ' signalé sur ' . $emprunt->getMateriel()->getNom());
            $em->persist($alerte);
        }

        // Si prolongation → mise à jour date fin
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
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit_emprunt' . $emprunt->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $this->hydrateFromRequest($emprunt, $request, $materielRepo, $utilisateurRepo);
            $em->flush();
            $this->addFlash('success', 'Emprunt modifié.');
            return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
        }

        return $this->render('emprunt/form.html.twig', [
            'emprunt'      => $emprunt,
            'materiels'    => $materielRepo->findAll(),
            'utilisateurs' => $utilisateurRepo->findAll(),
        ]);
    }

    private function hydrateFromRequest(
        Emprunt $emprunt,
        Request $request,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
    ): void {
        $emprunt->setDateDebut(new \DateTime($request->request->get('date_debut')));
        $emprunt->setDateFinPrevue(new \DateTime($request->request->get('date_fin_prevue')));

        $materiel = $materielRepo->find($request->request->getInt('materiel_id'));
        if ($materiel) $emprunt->setMateriel($materiel);

        $utilisateur = $utilisateurRepo->find($request->request->getInt('utilisateur_id'));
        if ($utilisateur) $emprunt->setUtilisateur($utilisateur);
    }
}
