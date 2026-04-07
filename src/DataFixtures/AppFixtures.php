<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Profil;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ── CATÉGORIES ──────────────────────────────────────────
        $cats = [];
        $catData = [
            'Informatique standard' => 'PC, écrans, claviers, souris',
            'Matériel audiovisuel'  => 'Caméras, micros, éclairages',
            'Réalité virtuelle'     => 'Casques VR, contrôleurs',
            'Mobilier'              => 'Tables, chaises, présentoirs',
            'Outillage'             => 'Perceuses, tournevis, matériel technique',
            'Véhicules'             => 'Voitures de service, vélos',
        ];

        foreach ($catData as $nom => $desc) {
            $cat = new Categorie();
            $cat->setNom($nom);
            $cat->setDescription($desc);
            $manager->persist($cat);
            $cats[$nom] = $cat;
        }

        // ── PROFILS + AUTORISATIONS ──────────────────────────────
        $profilData = [
            'Administrateur' => [
                'desc' => 'Accès total au système',
                'cats' => array_keys($catData),
            ],
            'Gestionnaire' => [
                'desc' => 'Gère le parc, valide les emprunts',
                'cats' => array_keys($catData),
            ],
            'Employé' => [
                'desc' => 'Peut emprunter tout le matériel standard',
                'cats' => ['Informatique standard', 'Matériel audiovisuel', 'Mobilier'],
            ],
            'Stagiaire' => [
                'desc' => 'Accès limité, pas de matériel sensible',
                'cats' => ['Informatique standard'],
            ],
            'Client' => [
                'desc' => 'Accès minimal, matériel basique uniquement',
                'cats' => ['Mobilier', 'Informatique standard'],
            ],
        ];

        $profils = [];
        foreach ($profilData as $nom => $data) {
            $profil = new Profil();
            $profil->setNom($nom);
            $profil->setDescription($data['desc']);
            foreach ($data['cats'] as $catNom) {
                $profil->addCategorie($cats[$catNom]);
            }
            $manager->persist($profil);
            $profils[$nom] = $profil;
        }

        // ── UTILISATEUR ADMIN ────────────────────────────────────
        $admin = new Utilisateur();
        $admin->setNom('LOCMNS');
        $admin->setPrenom('Admin');
        $admin->setEmail('admin@locmns.fr');
        $admin->setMotDePasse(password_hash('1234', PASSWORD_BCRYPT));
        $admin->setProfil($profils['Administrateur']);
        $manager->persist($admin);

        $manager->flush();
    }
}
