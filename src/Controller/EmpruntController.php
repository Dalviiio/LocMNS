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
use App\Service\AutorisationService;
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
        $raw    = $request->query->all();
        $search = isset($raw['search']) ? (string) $raw['search'] : '';
        $statut = isset($raw['statut']) ? (string) $raw['statut'] : '';

        return $this->render('emprunt/index.html.twig', [
            'emprunts'      => $repo->findWithFilters($search ?: null, $statut ?: null),
            'statuts'       => StatutEmprunt::cases(),
            'filtre_search' => $search,
            'filtre_statut' => $statut,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MaterielRepository $materielRepo,
        UtilisateurRepository $utilisateurRepo,
        AutorisationService $auth,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('new_emprunt', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $emprunt = new Emprunt();
            $this->hydrateFromRequest($emprunt, $request, $materielRepo, $utilisateurRepo);

            // Vérification accès matériel
            if ($emprunt->getMateriel()) {
                $auth->verifierAccesMateriel($emprunt->getMateriel());
            }

            $em->persist($emprunt);

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

        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('emprunt/form.html.twig', [
            'emprunt'      => null,
            'materiels'    => $materiels,
            'utilisateurs' => $utilisateurRepo->findAll(),
            'acces_limite' => !empty($categoriesAutorisees) && count($materiels) === 0,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Emprunt $emprunt): Response
    {
        return $this->render('emprunt/show.html.twig', [
            'emprunt'         => $emprunt,
            'types_evenement' => TypeEvenement::cases(),
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

        if (in_array($evenement->getType(), [TypeEvenement::Panne, TypeEvenement::Dysfonctionnement])) {
            $alerte = new Alerte();
            $alerte->setUtilisateur($emprunt->getUtilisateur());
            $alerte->setEmprunt($emprunt);
            $alerte->setType(TypeAlerte::Panne);
            $alerte->setMessage($evenement->getType()->label() . ' signalé sur ' . $emprunt->getMateriel()->getNom());
            $em->persist($alerte);
        }

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
        AutorisationService $auth,
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

        $categoriesAutorisees = $auth->getCategoriesAutorisees();
        $materiels = $categoriesAutorisees
            ? $materielRepo->findByCategories($categoriesAutorisees)
            : $materielRepo->findAll();

        return $this->render('emprunt/form.html.twig', [
            'emprunt'      => $emprunt,
            'materiels'    => $materiels,
            'utilisateurs' => $utilisateurRepo->findAll(),
            'acces_limite' => false,
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
