-- ============================================================
-- LOC MNS — Données de démonstration
-- Exécuter APRÈS les migrations Doctrine
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- PROFILS
-- ============================================================
INSERT INTO profil (nom, description) VALUES
  ('Administrateur',  'Accès total à toutes les fonctionnalités'),
  ('Gestionnaire',    'Gestion du parc matériel et supervision des emprunts'),
  ('Employé',         'Emprunt de matériel selon les catégories autorisées'),
  ('Stagiaire',       'Accès limité au matériel bureautique'),
  ('Client',          'Accès au matériel de présentation uniquement');

-- ============================================================
-- CATÉGORIES
-- ============================================================
INSERT INTO categorie (nom, description) VALUES
  ('Informatique',    'PC portables, postes fixes, périphériques'),
  ('Réseau',          'Switches, routeurs, câbles réseau'),
  ('Présentation',    'Vidéoprojecteurs, pointeurs, micros'),
  ('Accessoires',     'Souris, claviers, adaptateurs, câbles'),
  ('Monocartes',      'Raspberry Pi et cartes de développement'),
  ('Audio/Vidéo',     'Webcams, microphones, matériel AV');

-- ============================================================
-- PROFIL_CATEGORIE — autorisations
-- ============================================================
INSERT INTO profil_categorie (id_profil, id_categorie) VALUES
  -- Employé : Informatique + Réseau + Accessoires
  ((SELECT id FROM profil WHERE nom = 'Employé'), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ((SELECT id FROM profil WHERE nom = 'Employé'), (SELECT id FROM categorie WHERE nom = 'Réseau')),
  ((SELECT id FROM profil WHERE nom = 'Employé'), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  -- Stagiaire : Informatique + Accessoires
  ((SELECT id FROM profil WHERE nom = 'Stagiaire'), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ((SELECT id FROM profil WHERE nom = 'Stagiaire'), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  -- Client : Présentation uniquement
  ((SELECT id FROM profil WHERE nom = 'Client'), (SELECT id FROM categorie WHERE nom = 'Présentation'));

-- ============================================================
-- MATÉRIELS — 92 items
-- ============================================================

-- PC Portables Dell Latitude 5540 × 5
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Dell Latitude 5540', 'DL5540-001', 'Bon',  'Salle B204', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell Latitude 5540', 'DL5540-002', 'Bon',  'Salle B204', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell Latitude 5540', 'DL5540-003', 'Neuf', 'Réserve',    NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell Latitude 5540', 'DL5540-004', 'Use',  'Salle A101', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell Latitude 5540', 'DL5540-005', 'Hs',   'Maintenance', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique'));

-- PC Portables Lenovo ThinkPad L14 × 4
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Lenovo ThinkPad L14', 'TP-L14-001', 'Bon',  'Salle B204', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Lenovo ThinkPad L14', 'TP-L14-002', 'Neuf', 'Réserve',    NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Lenovo ThinkPad L14', 'TP-L14-003', 'Bon',  'Salle A101', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Lenovo ThinkPad L14', 'TP-L14-004', 'Use',  'Salle C302', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique'));

-- MacBook Air M2 × 3
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('MacBook Air M2', 'MBA-M2-001', 'Neuf', 'Réserve',    NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('MacBook Air M2', 'MBA-M2-002', 'Bon',  'Direction',  NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('MacBook Air M2', 'MBA-M2-003', 'Bon',  'Salle A101', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique'));

-- HP EliteDesk 800 G6 × 4
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('HP EliteDesk 800 G6', 'HP-ED800-001', 'Bon',  'Salle B204', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('HP EliteDesk 800 G6', 'HP-ED800-002', 'Bon',  'Salle B204', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('HP EliteDesk 800 G6', 'HP-ED800-003', 'Neuf', 'Réserve',    NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('HP EliteDesk 800 G6', 'HP-ED800-004', 'Use',  'Salle C302', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique'));

-- Dell OptiPlex 7010 × 3
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Dell OptiPlex 7010', 'DO-7010-001', 'Bon',  'Salle A101', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell OptiPlex 7010', 'DO-7010-002', 'Use',  'Salle A101', NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique')),
  ('Dell OptiPlex 7010', 'DO-7010-003', 'Neuf', 'Réserve',    NOW(), (SELECT id FROM categorie WHERE nom = 'Informatique'));

-- Switch Cisco SG110-16 × 3
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Switch Cisco SG110-16', 'SW-CIS-001', 'Bon',  'Baie réseau B', NOW(), (SELECT id FROM categorie WHERE nom = 'Réseau')),
  ('Switch Cisco SG110-16', 'SW-CIS-002', 'Bon',  'Baie réseau A', NOW(), (SELECT id FROM categorie WHERE nom = 'Réseau')),
  ('Switch Cisco SG110-16', 'SW-CIS-003', 'Neuf', 'Réserve',       NOW(), (SELECT id FROM categorie WHERE nom = 'Réseau'));

-- Routeur TP-Link AX55 × 2
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Routeur TP-Link AX55', 'RT-TPL-001', 'Bon',  'Salle réseau', NOW(), (SELECT id FROM categorie WHERE nom = 'Réseau')),
  ('Routeur TP-Link AX55', 'RT-TPL-002', 'Neuf', 'Réserve',      NOW(), (SELECT id FROM categorie WHERE nom = 'Réseau'));

-- Raspberry Pi 4 4Go × 6
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Raspberry Pi 4 4Go', 'RPI4-001', 'Bon',  'Labo IoT', NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes')),
  ('Raspberry Pi 4 4Go', 'RPI4-002', 'Bon',  'Labo IoT', NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes')),
  ('Raspberry Pi 4 4Go', 'RPI4-003', 'Neuf', 'Réserve',  NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes')),
  ('Raspberry Pi 4 4Go', 'RPI4-004', 'Bon',  'Labo IoT', NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes')),
  ('Raspberry Pi 4 4Go', 'RPI4-005', 'Use',  'Labo IoT', NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes')),
  ('Raspberry Pi 4 4Go', 'RPI4-006', 'Hs',   'Maintenance', NOW(), (SELECT id FROM categorie WHERE nom = 'Monocartes'));

-- Vidéoprojecteur Epson EB-W51 × 4
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-001', 'Bon',  'Salle conférence A', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-002', 'Bon',  'Salle conférence B', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-003', 'Neuf', 'Réserve',            NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-004', 'Use',  'Salle A101',         NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation'));

-- Pointeur Logitech R500 × 6
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Pointeur Logitech R500', 'PT-LOG-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Pointeur Logitech R500', 'PT-LOG-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Pointeur Logitech R500', 'PT-LOG-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Pointeur Logitech R500', 'PT-LOG-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Pointeur Logitech R500', 'PT-LOG-005', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation')),
  ('Pointeur Logitech R500', 'PT-LOG-006', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Présentation'));

-- Micro USB Blue Yeti × 4
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Micro USB Blue Yeti', 'MIC-BY-001', 'Bon',  'Studio audio', NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Micro USB Blue Yeti', 'MIC-BY-002', 'Bon',  'Studio audio', NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Micro USB Blue Yeti', 'MIC-BY-003', 'Neuf', 'Réserve',      NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Micro USB Blue Yeti', 'MIC-BY-004', 'Use',  'Studio audio', NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo'));

-- Webcam Logitech C920 × 6
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Webcam Logitech C920', 'WC-LOG-001', 'Bon',  'Salle conf A', NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Webcam Logitech C920', 'WC-LOG-002', 'Bon',  'Salle conf B', NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Webcam Logitech C920', 'WC-LOG-003', 'Neuf', 'Réserve',      NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Webcam Logitech C920', 'WC-LOG-004', 'Bon',  'Réserve',      NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Webcam Logitech C920', 'WC-LOG-005', 'Use',  'Salle A101',   NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo')),
  ('Webcam Logitech C920', 'WC-LOG-006', 'Bon',  'Réserve',      NOW(), (SELECT id FROM categorie WHERE nom = 'Audio/Vidéo'));

-- Souris Logitech MX × 10
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Souris Logitech MX', 'MS-LOG-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-005', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-006', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-007', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-008', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-009', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Souris Logitech MX', 'MS-LOG-010', 'Hs',   'Maintenance', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires'));

-- Clavier Logitech K380 × 8
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Clavier Logitech K380', 'KB-LOG-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-005', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-006', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-007', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Clavier Logitech K380', 'KB-LOG-008', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires'));

-- Câble RJ45 5m × 10
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Câble RJ45 5m', 'RJ45-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-005', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-006', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-007', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-008', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-009', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Câble RJ45 5m', 'RJ45-010', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires'));

-- Adaptateur USB-C HDMI × 8
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-005', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-006', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-007', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C HDMI', 'ADP-HDMI-008', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires'));

-- Adaptateur USB-C RJ45 × 6
INSERT INTO materiel (nom, numero_serie, etat, localisation, created_at, categorie_id) VALUES
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-001', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-002', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-003', 'Neuf', 'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-004', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-005', 'Use',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires')),
  ('Adaptateur USB-C RJ45', 'ADP-RJ45-006', 'Bon',  'Réserve', NOW(), (SELECT id FROM categorie WHERE nom = 'Accessoires'));

-- ============================================================
-- MATERIEL_ACCESSOIRE
-- PC portables → Souris, Clavier, RJ45, ADP-HDMI, ADP-RJ45
-- ============================================================
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
SELECT m.id, a.id FROM materiel m, materiel a
WHERE m.numero_serie IN ('DL5540-001','DL5540-002','DL5540-003','DL5540-004','DL5540-005',
                         'TP-L14-001','TP-L14-002','TP-L14-003','TP-L14-004',
                         'MBA-M2-001','MBA-M2-002','MBA-M2-003')
  AND a.numero_serie IN ('MS-LOG-001','MS-LOG-002','MS-LOG-003',
                         'KB-LOG-001','KB-LOG-002','KB-LOG-003',
                         'RJ45-001','RJ45-002',
                         'ADP-HDMI-001','ADP-HDMI-002',
                         'ADP-RJ45-001','ADP-RJ45-002');

-- Postes fixes → Souris, Clavier, RJ45
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
SELECT m.id, a.id FROM materiel m, materiel a
WHERE m.numero_serie IN ('HP-ED800-001','HP-ED800-002','HP-ED800-003','HP-ED800-004',
                         'DO-7010-001','DO-7010-002','DO-7010-003')
  AND a.numero_serie IN ('MS-LOG-004','MS-LOG-005',
                         'KB-LOG-004','KB-LOG-005',
                         'RJ45-003','RJ45-004');

-- Vidéoprojecteurs → ADP-HDMI, Pointeur R500
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
SELECT m.id, a.id FROM materiel m, materiel a
WHERE m.numero_serie IN ('VP-EPS-001','VP-EPS-002','VP-EPS-003','VP-EPS-004')
  AND a.numero_serie IN ('ADP-HDMI-003','ADP-HDMI-004','PT-LOG-001','PT-LOG-002');

-- Raspberry Pi → RJ45, ADP-HDMI
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
SELECT m.id, a.id FROM materiel m, materiel a
WHERE m.numero_serie IN ('RPI4-001','RPI4-002','RPI4-003','RPI4-004','RPI4-005','RPI4-006')
  AND a.numero_serie IN ('RJ45-005','RJ45-006','ADP-HDMI-005','ADP-HDMI-006');

-- ============================================================
-- UTILISATEURS
-- Mot de passe admin/gestionnaire : admin123 / gest123
-- Autres : user123
-- ============================================================
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, created_at, profil_id) VALUES
  ('Dupont',    'Alice',   'admin@locmns.fr',       '$2y$12$N6XnExbE4Uqfw5ttYpro4eI4wUl1Yt5NIOdR2Bq2lJwAiMnfwLJMW', NOW(), (SELECT id FROM profil WHERE nom = 'Administrateur')),
  ('Martin',    'Baptiste','gestionnaire@locmns.fr','$2y$12$gCCcj19BMVbxk54h1EOh7.yEMPhqwmj8tuARdSotR126HNEKckjjq', NOW(), (SELECT id FROM profil WHERE nom = 'Gestionnaire')),
  ('Bernard',   'Claire',  'c.bernard@locmns.fr',   '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Thomas',    'David',   'd.thomas@locmns.fr',    '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Robert',    'Emma',    'e.robert@locmns.fr',    '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Richard',   'François','f.richard@locmns.fr',   '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Petit',     'Gaelle',  'g.petit@locmns.fr',     '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Stagiaire')),
  ('Durand',    'Hugo',    'h.durand@locmns.fr',    '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Stagiaire')),
  ('Moreau',    'Inès',    'i.moreau@locmns.fr',    '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Stagiaire')),
  ('Laurent',   'Julien',  'j.laurent@locmns.fr',   '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Client')),
  ('Simon',     'Karen',   'k.simon@locmns.fr',     '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Client')),
  ('Michel',    'Luc',     'l.michel@locmns.fr',    '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Leroy',     'Marie',   'm.leroy@locmns.fr',     '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Employé')),
  ('Roux',      'Nicolas', 'n.roux@locmns.fr',      '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Stagiaire')),
  ('Fournier',  'Olivia',  'o.fournier@locmns.fr',  '$2y$12$RWkWbFVn14ESfphFJLZqTO2B87uOE9roAAVzs1.nxP.NCbWrV0aES', NOW(), (SELECT id FROM profil WHERE nom = 'Client'));

-- ============================================================
-- EMPRUNTS — 15 emprunts variés
-- ============================================================
INSERT INTO emprunt (utilisateur_id, materiel_id, date_debut, date_fin_prevue, date_retour, statut, notes, created_at) VALUES
  -- En cours normaux
  ((SELECT id FROM utilisateur WHERE email = 'c.bernard@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'DL5540-001'),
   DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY), NULL, 'EnCours',
   'Formation en salle B204', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'd.thomas@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'TP-L14-001'),
   DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 6 DAY), NULL, 'EnCours',
   'Mission client externe', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'e.robert@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-001'),
   DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 'EnCours',
   'Présentation direction', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'g.petit@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'DL5540-002'),
   NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), NULL, 'EnCours',
   'Stage développement', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'h.durand@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'TP-L14-003'),
   DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY), NULL, 'EnCours',
   NULL, NOW()),

  -- En retard
  ((SELECT id FROM utilisateur WHERE email = 'f.richard@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'MBA-M2-002'),
   DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), NULL, 'Retard',
   'Retard signalé', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'l.michel@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'RPI4-001'),
   DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY), NULL, 'Retard',
   'Projet IoT prolongé', NOW()),

  -- Rendus
  ((SELECT id FROM utilisateur WHERE email = 'c.bernard@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-002'),
   DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY),
   DATE_SUB(NOW(), INTERVAL 10 DAY), 'Rendu', 'Séminaire annuel', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'm.leroy@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'DL5540-003'),
   DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY),
   DATE_SUB(NOW(), INTERVAL 15 DAY), 'Rendu', NULL, NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'd.thomas@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'HP-ED800-001'),
   DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY),
   DATE_SUB(NOW(), INTERVAL 21 DAY), 'Rendu', NULL, NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'e.robert@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'SW-CIS-001'),
   DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY),
   DATE_SUB(NOW(), INTERVAL 25 DAY), 'Rendu', 'Atelier réseau', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'j.laurent@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-003'),
   DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY),
   DATE_SUB(NOW(), INTERVAL 5 DAY), 'Rendu', 'Présentation client', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'n.roux@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'RPI4-002'),
   DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY),
   DATE_SUB(NOW(), INTERVAL 8 DAY), 'Rendu', 'Projet fin de stage', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'i.moreau@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'DL5540-004'),
   DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), NULL, 'EnCours',
   NULL, NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'k.simon@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'PT-LOG-001'),
   DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 'EnCours',
   'Démonstration produit', NOW());

-- ============================================================
-- RÉSERVATIONS — 10
-- ============================================================
INSERT INTO reservation (utilisateur_id, materiel_id, date_debut, date_fin, statut, created_at) VALUES
  ((SELECT id FROM utilisateur WHERE email = 'c.bernard@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'MBA-M2-001'),
   DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 'EnAttente', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'd.thomas@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-002'),
   DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY), 'Confirmee', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'e.robert@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'SW-CIS-002'),
   DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'EnAttente', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'j.laurent@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-003'),
   DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 11 DAY), 'Confirmee', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'g.petit@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'TP-L14-002'),
   DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 'EnAttente', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'k.simon@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-004'),
   DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 15 DAY), 'EnAttente', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'l.michel@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'DL5540-003'),
   DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 'Annulee', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'm.leroy@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'MBA-M2-003'),
   DATE_ADD(NOW(), INTERVAL 20 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY), 'EnAttente', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'n.roux@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'RPI4-003'),
   DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 'Confirmee', NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'o.fournier@locmns.fr'),
   (SELECT id FROM materiel WHERE numero_serie = 'PT-LOG-003'),
   DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 'EnAttente', NOW());

-- ============================================================
-- ALERTES — 10
-- ============================================================
INSERT INTO alerte (utilisateur_id, emprunt_id, type, message, lu, created_at) VALUES
  ((SELECT id FROM utilisateur WHERE email = 'admin@locmns.fr'), NULL,
   'NouvelleDemande', 'Claire Bernard a créé un nouvel emprunt : Dell Latitude 5540', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'gestionnaire@locmns.fr'), NULL,
   'NouvelleDemande', 'David Thomas emprunte : Lenovo ThinkPad L14', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'admin@locmns.fr'),
   (SELECT id FROM emprunt WHERE statut = 'Retard' LIMIT 1),
   'Retard', 'François Richard n''a pas rendu : MacBook Air M2 (retard de 3 jours)', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'gestionnaire@locmns.fr'),
   (SELECT id FROM emprunt WHERE statut = 'Retard' LIMIT 1),
   'Retard', 'François Richard n''a pas rendu : MacBook Air M2 (retard de 3 jours)', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'admin@locmns.fr'), NULL,
   'Panne', 'Panne signalée sur : Raspberry Pi 4 4Go (RPI4-001)', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'gestionnaire@locmns.fr'), NULL,
   'Panne', 'Panne signalée sur : Raspberry Pi 4 4Go (RPI4-001)', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

  ((SELECT id FROM utilisateur WHERE email = 'admin@locmns.fr'), NULL,
   'NouvelleDemande', 'Karen Simon demande une réservation : Pointeur Logitech R500', 0, NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'gestionnaire@locmns.fr'), NULL,
   'NouvelleDemande', 'Gaelle Petit emprunte : Dell Latitude 5540', 1, DATE_SUB(NOW(), INTERVAL 1 HOUR)),

  ((SELECT id FROM utilisateur WHERE email = 'admin@locmns.fr'), NULL,
   'Retard', 'Luc Michel n''a pas rendu : Raspberry Pi 4 4Go (retard de 7 jours)', 0, NOW()),

  ((SELECT id FROM utilisateur WHERE email = 'gestionnaire@locmns.fr'), NULL,
   'NouvelleDemande', 'Nicolas Roux réserve : Raspberry Pi 4 4Go', 0, NOW());

-- ============================================================
-- DOCUMENTS — 14
-- ============================================================
INSERT INTO document (materiel_id, type, titre, url, created_at) VALUES
  ((SELECT id FROM materiel WHERE numero_serie = 'DL5540-001'),
   'Notice', 'Guide utilisateur Dell Latitude 5540', 'https://www.dell.com/support/latitude-5540-guide', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'DL5540-001'),
   'DocTechnique', 'Fiche technique Dell Latitude 5540', 'https://www.dell.com/support/latitude-5540-spec', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'TP-L14-001'),
   'Notice', 'Manuel Lenovo ThinkPad L14', 'https://support.lenovo.com/thinkpad-l14-manual', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'MBA-M2-001'),
   'Notice', 'Guide MacBook Air M2', 'https://support.apple.com/macbook-air-m2', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'MBA-M2-001'),
   'Video', 'Prise en main MacBook Air M2', 'https://www.youtube.com/watch?v=macbook-air-m2-demo', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-001'),
   'Notice', 'Manuel Epson EB-W51', 'https://epson.fr/support/eb-w51-manuel', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'VP-EPS-001'),
   'DocTechnique', 'Fiche technique Epson EB-W51', 'https://epson.fr/support/eb-w51-spec', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'SW-CIS-001'),
   'DocTechnique', 'Guide configuration Cisco SG110-16', 'https://cisco.com/support/sg110-16-config', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'RPI4-001'),
   'Notice', 'Documentation Raspberry Pi 4', 'https://www.raspberrypi.com/documentation/', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'RPI4-001'),
   'Video', 'Démarrage Raspberry Pi 4', 'https://www.youtube.com/watch?v=rpi4-setup', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'MIC-BY-001'),
   'Notice', 'Guide Blue Yeti', 'https://www.bluemic.com/support/yeti', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'WC-LOG-001'),
   'Notice', 'Guide Logitech C920', 'https://support.logi.com/hc/c920', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'HP-ED800-001'),
   'DocTechnique', 'Fiche technique HP EliteDesk 800 G6', 'https://support.hp.com/elitedesk-800-g6-spec', NOW()),

  ((SELECT id FROM materiel WHERE numero_serie = 'RT-TPL-001'),
   'DocTechnique', 'Guide TP-Link AX55', 'https://www.tp-link.com/support/ax55-guide', NOW());

SET FOREIGN_KEY_CHECKS = 1;
