<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial LOC MNS';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categorie (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE profil (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE profil_categorie (
            id_profil INT NOT NULL,
            id_categorie INT NOT NULL,
            INDEX IDX_PC_PROFIL (id_profil),
            INDEX IDX_PC_CAT (id_categorie),
            PRIMARY KEY(id_profil, id_categorie)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE profil_categorie
            ADD CONSTRAINT FK_PC_PROFIL FOREIGN KEY (id_profil) REFERENCES profil (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_PC_CAT FOREIGN KEY (id_categorie) REFERENCES categorie (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE utilisateur (
            id INT AUTO_INCREMENT NOT NULL,
            profil_id INT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_UTIL_EMAIL (email),
            INDEX IDX_UTIL_PROFIL (profil_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE utilisateur
            ADD CONSTRAINT FK_UTIL_PROFIL FOREIGN KEY (profil_id) REFERENCES profil (id)');

        $this->addSql('CREATE TABLE materiel (
            id INT AUTO_INCREMENT NOT NULL,
            categorie_id INT NOT NULL,
            nom VARCHAR(150) NOT NULL,
            numero_serie VARCHAR(100) DEFAULT NULL,
            etat VARCHAR(10) NOT NULL,
            localisation VARCHAR(200) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_MAT_SERIE (numero_serie),
            INDEX IDX_MAT_CAT (categorie_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE materiel
            ADD CONSTRAINT FK_MAT_CAT FOREIGN KEY (categorie_id) REFERENCES categorie (id)');

        $this->addSql('CREATE TABLE materiel_accessoire (
            materiel_id INT NOT NULL,
            accessoire_id INT NOT NULL,
            INDEX IDX_MA_MAT (materiel_id),
            INDEX IDX_MA_ACC (accessoire_id),
            PRIMARY KEY(materiel_id, accessoire_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE materiel_accessoire
            ADD CONSTRAINT FK_MA_MAT FOREIGN KEY (materiel_id) REFERENCES materiel (id),
            ADD CONSTRAINT FK_MA_ACC FOREIGN KEY (accessoire_id) REFERENCES materiel (id)');

        $this->addSql('CREATE TABLE emprunt (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            materiel_id INT NOT NULL,
            parent_id INT DEFAULT NULL,
            date_debut DATETIME NOT NULL,
            date_fin_prevue DATETIME NOT NULL,
            date_retour DATETIME DEFAULT NULL,
            statut VARCHAR(20) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_EMP_USER (utilisateur_id),
            INDEX IDX_EMP_MAT (materiel_id),
            INDEX IDX_EMP_PARENT (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE emprunt
            ADD CONSTRAINT FK_EMP_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            ADD CONSTRAINT FK_EMP_MAT FOREIGN KEY (materiel_id) REFERENCES materiel (id),
            ADD CONSTRAINT FK_EMP_PARENT FOREIGN KEY (parent_id) REFERENCES emprunt (id)');

        $this->addSql('CREATE TABLE reservation (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            materiel_id INT NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin DATETIME NOT NULL,
            statut VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_RES_USER (utilisateur_id),
            INDEX IDX_RES_MAT (materiel_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE reservation
            ADD CONSTRAINT FK_RES_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            ADD CONSTRAINT FK_RES_MAT FOREIGN KEY (materiel_id) REFERENCES materiel (id)');

        $this->addSql('CREATE TABLE evenement (
            id INT AUTO_INCREMENT NOT NULL,
            emprunt_id INT NOT NULL,
            type VARCHAR(30) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_EVT_EMP (emprunt_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE evenement
            ADD CONSTRAINT FK_EVT_EMP FOREIGN KEY (emprunt_id) REFERENCES emprunt (id)');

        $this->addSql('CREATE TABLE alerte (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            emprunt_id INT DEFAULT NULL,
            type VARCHAR(20) NOT NULL,
            message LONGTEXT NOT NULL,
            lu TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX IDX_ALE_USER (utilisateur_id),
            INDEX IDX_ALE_EMP (emprunt_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE alerte
            ADD CONSTRAINT FK_ALE_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            ADD CONSTRAINT FK_ALE_EMP FOREIGN KEY (emprunt_id) REFERENCES emprunt (id)');

        $this->addSql('CREATE TABLE document (
            id INT AUTO_INCREMENT NOT NULL,
            materiel_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            titre VARCHAR(200) NOT NULL,
            url VARCHAR(500) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_DOC_MAT (materiel_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE document
            ADD CONSTRAINT FK_DOC_MAT FOREIGN KEY (materiel_id) REFERENCES materiel (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profil_categorie DROP FOREIGN KEY FK_PC_PROFIL');
        $this->addSql('ALTER TABLE profil_categorie DROP FOREIGN KEY FK_PC_CAT');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_UTIL_PROFIL');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_MAT_CAT');
        $this->addSql('ALTER TABLE materiel_accessoire DROP FOREIGN KEY FK_MA_MAT');
        $this->addSql('ALTER TABLE materiel_accessoire DROP FOREIGN KEY FK_MA_ACC');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_EMP_USER');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_EMP_MAT');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_EMP_PARENT');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_RES_USER');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_RES_MAT');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_EVT_EMP');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_ALE_USER');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_ALE_EMP');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_DOC_MAT');

        $this->addSql('DROP TABLE IF EXISTS document');
        $this->addSql('DROP TABLE IF EXISTS alerte');
        $this->addSql('DROP TABLE IF EXISTS evenement');
        $this->addSql('DROP TABLE IF EXISTS reservation');
        $this->addSql('DROP TABLE IF EXISTS emprunt');
        $this->addSql('DROP TABLE IF EXISTS materiel_accessoire');
        $this->addSql('DROP TABLE IF EXISTS materiel');
        $this->addSql('DROP TABLE IF EXISTS utilisateur');
        $this->addSql('DROP TABLE IF EXISTS profil_categorie');
        $this->addSql('DROP TABLE IF EXISTS profil');
        $this->addSql('DROP TABLE IF EXISTS categorie');
    }
}
