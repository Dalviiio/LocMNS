<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Evenement;
use App\Entity\StatutEmprunt;
use App\Entity\TypeAlerte;
use App\Entity\TypeEvenement;
use App\Repository\EmpruntRepository;
use App\Repository\MaterielRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AlerteService;
use App\Service\AutorisationService;
use App\Service\RoleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/emprunts')]
class EmpruntController extends AbstractAppController
{
    public function __construct(
        UtilisateurRepository $utilisateurRepo,
        private EmpruntRepository     $empruntRepo,
        private MaterielRepository    $materielRepo,
        private AutorisationService   $autorisationService,
        private AlerteService         $alerteService,
        private RoleService           $roleService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($utilisateurRepo);
    }

    #[Route('', name: 'emprunt_index')]
    public function index(Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);

        $search = $request->query->get('search');
        $statut = $request->query->get('statut');
        $userId = $context['isGestionnaire'] ? null : $user->getId();

        $emprunts = $this->empruntRepo->findWithFilters($search, $statut, $userId);

        return $this->render('emprunt/index.html.twig', array_merge($context, [
            'emprunts' => $emprunts,
            'statuts'  => StatutEmprunt::cases(),
            'search'   => $search,
            'statutFiltre' => $statut,
        ]));
    }

    #[Route('/nouveau', name: 'emprunt_new', methods: ['GET', 'POST'])]
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
            if (!$this->isCsrfTokenValid('emprunt_new', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $materiel = $this->materielRepo->find($request->request->getInt('materiel_id'));
            if (!$materiel) {
                $this->addFlash('error', 'Matériel introuvable.');
                return $this->redirectToRoute('emprunt_new');
            }

            $this->autorisationService->verifierOuRefuser($user, $materiel);

            $emprunt = new Emprunt();
            $emprunt->setUtilisateur($user);
            $emprunt->setMateriel($materiel);
            $emprunt->setDateDebut(new \DateTime($request->request->get('date_debut')));
            $emprunt->setDateFinPrevue(new \DateTime($request->request->get('date_fin_prevue')));
            $emprunt->setStatut(StatutEmprunt::EnCours);
            $emprunt->setNotes($request->request->get('notes') ?: null);
            $this->em->persist($emprunt);

            // Accessoires
            foreach ($request->request->all('accessoires') as $accId) {
                $acc = $this->materielRepo->find((int) $accId);
                if ($acc) {
                    $empAcc = new Emprunt();
                    $empAcc->setUtilisateur($user);
                    $empAcc->setMateriel($acc);
                    $empAcc->setDateDebut($emprunt->getDateDebut());
                    $empAcc->setDateFinPrevue($emprunt->getDateFinPrevue());
                    $empAcc->setStatut(StatutEmprunt::EnCours);
                    $empAcc->setParentEmprunt($emprunt);
                    $this->em->persist($empAcc);
                }
            }

            // Alerte gestionnaires
            foreach ($this->utilisateurRepo->findAll() as $u) {
                if (in_array($u->getProfil()->getNom(), ['Administrateur', 'Gestionnaire'], true)) {
                    $this->alerteService->creer(
                        $u,
                        TypeAlerte::NouvelleDemande,
                        $user->getNomComplet() . ' emprunte : ' . $materiel->getNom(),
                        $emprunt
                    );
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Emprunt créé avec succès.');
            return $this->redirectToRoute('emprunt_show', ['id' => $emprunt->getId()]);
        }

        return $this->render('emprunt/new.html.twig', array_merge($context, [
            'materiels' => $materiels,
        ]));
    }

    #[Route('/{id}', name: 'emprunt_show', requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);
        $emprunt = $this->empruntRepo->find($id);

        if (!$emprunt) { throw $this->createNotFoundException(); }
        if (!$context['isGestionnaire'] && $emprunt->getUtilisateur()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('emprunt/show.html.twig', array_merge($context, [
            'emprunt'         => $emprunt,
            'typeEvenements'  => TypeEvenement::cases(),
        ]));
    }

    #[Route('/{id}/retour', name: 'emprunt_retour', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function retour(int $id, Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);
        $emprunt = $this->empruntRepo->find($id);

        if (!$emprunt) { throw $this->createNotFoundException(); }
        if (!$context['isGestionnaire'] && $emprunt->getUtilisateur()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('emprunt_retour_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $emprunt->setStatut(StatutEmprunt::Rendu);
        $emprunt->setDateRetour(new \DateTime());

        foreach ($emprunt->getEmpruntsAccessoires() as $empAcc) {
            $empAcc->setStatut(StatutEmprunt::Rendu);
            $empAcc->setDateRetour(new \DateTime());
        }

        $this->em->flush();
        $this->addFlash('success', 'Retour enregistré.');
        return $this->redirectToRoute('emprunt_show', ['id' => $id]);
    }

    #[Route('/{id}/evenement', name: 'emprunt_evenement', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function ajouterEvenement(int $id, Request $request): Response
    {
        $user    = $this->getUtilisateurConnecte($request);
        $context = $this->getProfilContext($request);
        $emprunt = $this->empruntRepo->find($id);

        if (!$emprunt) { throw $this->createNotFoundException(); }
        if (!$context['isGestionnaire'] && $emprunt->getUtilisateur()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('emprunt_evt_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $type       = TypeEvenement::from($request->request->get('type'));
        $evenement  = new Evenement();
        $evenement->setEmprunt($emprunt);
        $evenement->setType($type);
        $evenement->setDescription($request->request->get('description') ?: null);
        $this->em->persist($evenement);

        if (in_array($type, [TypeEvenement::Panne, TypeEvenement::Dysfonctionnement], true)) {
            foreach ($this->utilisateurRepo->findAll() as $u) {
                if (in_array($u->getProfil()->getNom(), ['Administrateur', 'Gestionnaire'], true)) {
                    $this->alerteService->creer($u, TypeAlerte::Panne,
                        $type->label() . ' signalé sur : ' . $emprunt->getMateriel()->getNom(),
                        $emprunt
                    );
                }
            }
        }

        $this->em->flush();
        $this->addFlash('success', 'Événement signalé.');
        return $this->redirectToRoute('emprunt_show', ['id' => $id]);
    }

    #[Route('/export', name: 'emprunt_export')]
    public function export(Request $request): Response
    {
        $context = $this->getProfilContext($request);
        if (!$context['isGestionnaire']) { throw $this->createAccessDeniedException(); }

        $this->getUtilisateurConnecte($request);
        $emprunts = $this->empruntRepo->findWithFilters();

        $csv = "ID;Utilisateur;Matériel;Début;Fin prévue;Retour;Statut\n";
        foreach ($emprunts as $e) {
            $csv .= implode(';', [
                $e->getId(),
                $e->getUtilisateur()->getNomComplet(),
                $e->getMateriel()->getNom(),
                $e->getDateDebut()->format('d/m/Y'),
                $e->getDateFinPrevue()->format('d/m/Y'),
                $e->getDateRetour() ? $e->getDateRetour()->format('d/m/Y') : '',
                $e->getStatut()->label(),
            ]) . "\n";
        }

        return new Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="emprunts.csv"',
        ]);
    }
}
