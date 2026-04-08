-- ═══════════════════════════════════════════════════════════════
--  LOCMNS — Seed complet
--  Compatible MySQL 8 | Exécuter dans phpMyAdmin sur la base locmns
--  Ne touche pas à l'utilisateur admin@locmns.fr
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Créer la table si elle n'existe pas encore (avant le nettoyage)
CREATE TABLE IF NOT EXISTS materiel_accessoire (
    materiel_id   INT NOT NULL,
    accessoire_id INT NOT NULL,
    PRIMARY KEY (materiel_id, accessoire_id),
    INDEX IDX_MA_MAT (materiel_id),
    INDEX IDX_MA_ACC (accessoire_id),
    CONSTRAINT FK_MA_MAT FOREIGN KEY (materiel_id)   REFERENCES materiel (id) ON DELETE CASCADE,
    CONSTRAINT FK_MA_ACC FOREIGN KEY (accessoire_id) REFERENCES materiel (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

START TRANSACTION;

-- ───────────────────────────────────────────────────────────────
-- NETTOYAGE (ordre inverse des FK)
-- ───────────────────────────────────────────────────────────────
DELETE FROM materiel_accessoire;
DELETE FROM document;
DELETE FROM alerte;
DELETE FROM evenement;
DELETE FROM reservation;
DELETE FROM emprunt;
DELETE FROM materiel;
DELETE FROM profil_categorie;
DELETE FROM categorie;
-- Profils : on recrée tout sauf on préserve l'utilisateur admin
-- (on supprime les profils APRÈS avoir détaché l'utilisateur admin)
UPDATE utilisateur SET profil_id = NULL WHERE email = 'admin@locmns.fr';
DELETE FROM profil;

-- ═══════════════════════════════════════════════════════════════
-- TÂCHE 1 — CATÉGORIES
-- ═══════════════════════════════════════════════════════════════

INSERT INTO categorie (nom, description) VALUES
    ('Ordinateurs portables', 'Laptops et ultrabooks pour usage nomade'),
    ('Postes fixes',          'Unités centrales et tout-en-un de bureau'),
    ('Réseau',                'Switchs, routeurs, Raspberry Pi et matériel réseau'),
    ('Présentation',          'Vidéoprojecteurs, pointeurs et écrans de présentation'),
    ('Audiovisuel',           'Micros, webcams, caméras et équipements AV'),
    ('Accessoires',           'Souris, claviers, câbles et adaptateurs');

-- ═══════════════════════════════════════════════════════════════
-- TÂCHE 2 — MATÉRIELS
-- ═══════════════════════════════════════════════════════════════

-- ── Ordinateurs portables ───────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('Dell Latitude 5540', 'DL5540-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Dell Latitude 5540', 'DL5540-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Dell Latitude 5540', 'DL5540-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Dell Latitude 5540', 'DL5540-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Dell Latitude 5540', 'DL5540-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),

    ('Lenovo ThinkPad L14', 'TP-L14-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Lenovo ThinkPad L14', 'TP-L14-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Lenovo ThinkPad L14', 'TP-L14-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('Lenovo ThinkPad L14', 'TP-L14-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),

    ('MacBook Air M2', 'MBA-M2-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('MacBook Air M2', 'MBA-M2-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW()),
    ('MacBook Air M2', 'MBA-M2-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Ordinateurs portables'), NOW());

-- ── Postes fixes ────────────────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('HP EliteDesk 800 G6', 'HP-ED800-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),
    ('HP EliteDesk 800 G6', 'HP-ED800-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),
    ('HP EliteDesk 800 G6', 'HP-ED800-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),
    ('HP EliteDesk 800 G6', 'HP-ED800-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),

    ('Dell OptiPlex 7010', 'DO-7010-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),
    ('Dell OptiPlex 7010', 'DO-7010-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW()),
    ('Dell OptiPlex 7010', 'DO-7010-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Postes fixes'), NOW());

-- ── Réseau ──────────────────────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('Switch Cisco SG110-16',      'SW-CIS-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Switch Cisco SG110-16',      'SW-CIS-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Switch Cisco SG110-16',      'SW-CIS-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),

    ('Routeur TP-Link Archer AX55', 'RT-TPL-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Routeur TP-Link Archer AX55', 'RT-TPL-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),

    ('Raspberry Pi 4 4Go', 'RPI4-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Raspberry Pi 4 4Go', 'RPI4-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Raspberry Pi 4 4Go', 'RPI4-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Raspberry Pi 4 4Go', 'RPI4-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Raspberry Pi 4 4Go', 'RPI4-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW()),
    ('Raspberry Pi 4 4Go', 'RPI4-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Réseau'), NOW());

-- ── Présentation ────────────────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Vidéoprojecteur Epson EB-W51', 'VP-EPS-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),

    ('Pointeur Logitech R500', 'PT-LOG-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Pointeur Logitech R500', 'PT-LOG-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Pointeur Logitech R500', 'PT-LOG-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Pointeur Logitech R500', 'PT-LOG-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Pointeur Logitech R500', 'PT-LOG-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW()),
    ('Pointeur Logitech R500', 'PT-LOG-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Présentation'), NOW());

-- ── Audiovisuel ─────────────────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('Micro USB Blue Yeti',    'MIC-BY-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Micro USB Blue Yeti',    'MIC-BY-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Micro USB Blue Yeti',    'MIC-BY-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Micro USB Blue Yeti',    'MIC-BY-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),

    ('Webcam Logitech C920',   'WC-LOG-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Webcam Logitech C920',   'WC-LOG-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Webcam Logitech C920',   'WC-LOG-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Webcam Logitech C920',   'WC-LOG-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Webcam Logitech C920',   'WC-LOG-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW()),
    ('Webcam Logitech C920',   'WC-LOG-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Audiovisuel'), NOW());

-- ── Accessoires ─────────────────────────────────────────────────
INSERT INTO materiel (nom, numero_serie, etat, localisation, categorie_id, created_at) VALUES
    ('Souris Logitech MX Anywhere', 'MS-LOG-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-007', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-008', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-009', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Souris Logitech MX Anywhere', 'MS-LOG-010', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),

    ('Clavier Logitech K380', 'KB-LOG-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-007', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Clavier Logitech K380', 'KB-LOG-008', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),

    ('Câble RJ45 5m', 'RJ45-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-007', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-008', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-009', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Câble RJ45 5m', 'RJ45-010', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),

    ('Adaptateur USB-C HDMI', 'ADP-HDMI-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-007', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C HDMI', 'ADP-HDMI-008', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),

    ('Adaptateur USB-C RJ45', 'ADP-RJ45-001', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C RJ45', 'ADP-RJ45-002', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C RJ45', 'ADP-RJ45-003', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C RJ45', 'ADP-RJ45-004', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C RJ45', 'ADP-RJ45-005', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW()),
    ('Adaptateur USB-C RJ45', 'ADP-RJ45-006', 'bon', 'Salle stockage MNS', (SELECT id FROM categorie WHERE nom = 'Accessoires'), NOW());

-- ═══════════════════════════════════════════════════════════════
-- TÂCHE 3 — PROFILS & AUTORISATIONS
-- ═══════════════════════════════════════════════════════════════

INSERT INTO profil (nom, description) VALUES
    ('Administrateur', 'Accès total au système'),
    ('Gestionnaire',   'Gère le parc, valide les emprunts'),
    ('Employé',        'Peut emprunter tout le matériel standard'),
    ('Stagiaire',      'Accès limité, pas de matériel sensible'),
    ('Client',         'Accès minimal, matériel basique uniquement');

-- Rattacher l'admin au nouveau profil Administrateur
UPDATE utilisateur
SET profil_id = (SELECT id FROM profil WHERE nom = 'Administrateur')
WHERE email = 'admin@locmns.fr';

-- Administrateur → toutes catégories
INSERT INTO profil_categorie (profil_id, categorie_id)
    SELECT p.id, c.id FROM profil p, categorie c
    WHERE p.nom = 'Administrateur';

-- Gestionnaire → toutes catégories
INSERT INTO profil_categorie (profil_id, categorie_id)
    SELECT p.id, c.id FROM profil p, categorie c
    WHERE p.nom = 'Gestionnaire';

-- Employé → Ordinateurs portables, Postes fixes, Présentation, Audiovisuel, Accessoires
INSERT INTO profil_categorie (profil_id, categorie_id)
    SELECT p.id, c.id FROM profil p, categorie c
    WHERE p.nom = 'Employé'
      AND c.nom IN ('Ordinateurs portables', 'Postes fixes', 'Présentation', 'Audiovisuel', 'Accessoires');

-- Stagiaire → Ordinateurs portables, Accessoires
INSERT INTO profil_categorie (profil_id, categorie_id)
    SELECT p.id, c.id FROM profil p, categorie c
    WHERE p.nom = 'Stagiaire'
      AND c.nom IN ('Ordinateurs portables', 'Accessoires');

-- Client → Présentation, Audiovisuel, Accessoires
INSERT INTO profil_categorie (profil_id, categorie_id)
    SELECT p.id, c.id FROM profil p, categorie c
    WHERE p.nom = 'Client'
      AND c.nom IN ('Présentation', 'Audiovisuel', 'Accessoires');

-- ═══════════════════════════════════════════════════════════════
-- TÂCHE 4 — TABLE ACCESSOIRES COMPATIBLES
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS materiel_accessoire (
    materiel_id   INT NOT NULL,
    accessoire_id INT NOT NULL,
    PRIMARY KEY (materiel_id, accessoire_id),
    INDEX IDX_MA_MAT (materiel_id),
    INDEX IDX_MA_ACC (accessoire_id),
    CONSTRAINT FK_MA_MAT FOREIGN KEY (materiel_id)   REFERENCES materiel (id) ON DELETE CASCADE,
    CONSTRAINT FK_MA_ACC FOREIGN KEY (accessoire_id) REFERENCES materiel (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

-- ── PC portables → Souris, Clavier, Câble RJ45, Adaptateur USB-C HDMI, Adaptateur USB-C RJ45
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
    SELECT m.id, a.id
    FROM materiel m
    CROSS JOIN materiel a
    JOIN categorie cm ON m.categorie_id = cm.id
    WHERE cm.nom = 'Ordinateurs portables'
    AND a.nom IN (
        'Souris Logitech MX Anywhere',
        'Clavier Logitech K380',
        'Câble RJ45 5m',
        'Adaptateur USB-C HDMI',
        'Adaptateur USB-C RJ45'
    );

-- ── Postes fixes → Souris, Clavier, Câble RJ45
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
    SELECT m.id, a.id
    FROM materiel m
    CROSS JOIN materiel a
    JOIN categorie cm ON m.categorie_id = cm.id
    WHERE cm.nom = 'Postes fixes'
    AND a.nom IN (
        'Souris Logitech MX Anywhere',
        'Clavier Logitech K380',
        'Câble RJ45 5m'
    );

-- ── Vidéoprojecteurs → Adaptateur USB-C HDMI, Pointeur Logitech R500
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
    SELECT m.id, a.id
    FROM materiel m
    CROSS JOIN materiel a
    WHERE m.nom = 'Vidéoprojecteur Epson EB-W51'
    AND a.nom IN (
        'Adaptateur USB-C HDMI',
        'Pointeur Logitech R500'
    );

-- ── Raspberry Pi → Câble RJ45, Adaptateur USB-C HDMI
INSERT INTO materiel_accessoire (materiel_id, accessoire_id)
    SELECT m.id, a.id
    FROM materiel m
    CROSS JOIN materiel a
    WHERE m.nom = 'Raspberry Pi 4 4Go'
    AND a.nom IN (
        'Câble RJ45 5m',
        'Adaptateur USB-C HDMI'
    );

-- ═══════════════════════════════════════════════════════════════
-- VÉRIFICATION
-- ═══════════════════════════════════════════════════════════════

-- SELECT c.nom, COUNT(m.id) as nb FROM materiel m JOIN categorie c ON m.categorie_id = c.id GROUP BY c.nom;
-- SELECT p.nom, COUNT(pc.categorie_id) as nb_cats FROM profil p LEFT JOIN profil_categorie pc ON p.id = pc.profil_id GROUP BY p.nom;
-- SELECT COUNT(*) as nb_associations FROM materiel_accessoire;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
