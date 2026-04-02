<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260001000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma initial LOCMNS';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE profil (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE categorie (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE profil_categorie (
            profil_id INT NOT NULL,
            categorie_id INT NOT NULL,
            PRIMARY KEY(profil_id, categorie_id),
            INDEX IDX_PROFIL (profil_id),
            INDEX IDX_CATEGORIE (categorie_id),
            CONSTRAINT FK_PC_PROFIL FOREIGN KEY (profil_id) REFERENCES profil (id) ON DELETE CASCADE,
            CONSTRAINT FK_PC_CATEGORIE FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE utilisateur (
            id INT AUTO_INCREMENT NOT NULL,
            profil_id INT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_EMAIL (email),
            INDEX IDX_PROFIL (profil_id),
            CONSTRAINT FK_U_PROFIL FOREIGN KEY (profil_id) REFERENCES profil (id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE materiel (
            id INT AUTO_INCREMENT NOT NULL,
            categorie_id INT NOT NULL,
            nom VARCHAR(150) NOT NULL,
            numero_serie VARCHAR(100) DEFAULT NULL,
            etat VARCHAR(10) NOT NULL,
            localisation VARCHAR(200) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_SERIE (numero_serie),
            INDEX IDX_CAT (categorie_id),
            CONSTRAINT FK_M_CAT FOREIGN KEY (categorie_id) REFERENCES categorie (id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE emprunt (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            materiel_id INT NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin_prevue DATETIME NOT NULL,
            date_retour DATETIME DEFAULT NULL,
            statut VARCHAR(20) NOT NULL,
            INDEX IDX_U (utilisateur_id),
            INDEX IDX_M (materiel_id),
            CONSTRAINT FK_E_U FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            CONSTRAINT FK_E_M FOREIGN KEY (materiel_id) REFERENCES materiel (id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE reservation (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            materiel_id INT NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin DATETIME NOT NULL,
            statut VARCHAR(20) NOT NULL,
            INDEX IDX_RU (utilisateur_id),
            INDEX IDX_RM (materiel_id),
            CONSTRAINT FK_R_U FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            CONSTRAINT FK_R_M FOREIGN KEY (materiel_id) REFERENCES materiel (id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE evenement (
            id INT AUTO_INCREMENT NOT NULL,
            emprunt_id INT NOT NULL,
            type VARCHAR(30) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_EV_E (emprunt_id),
            CONSTRAINT FK_EV_E FOREIGN KEY (emprunt_id) REFERENCES emprunt (id) ON DELETE CASCADE,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE alerte (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            emprunt_id INT DEFAULT NULL,
            type VARCHAR(30) NOT NULL,
            message LONGTEXT NOT NULL,
            lu TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX IDX_AL_U (utilisateur_id),
            INDEX IDX_AL_E (emprunt_id),
            CONSTRAINT FK_AL_U FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
            CONSTRAINT FK_AL_E FOREIGN KEY (emprunt_id) REFERENCES emprunt (id) ON DELETE SET NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE document (
            id INT AUTO_INCREMENT NOT NULL,
            materiel_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            titre VARCHAR(200) NOT NULL,
            url VARCHAR(500) NOT NULL,
            INDEX IDX_DOC_M (materiel_id),
            CONSTRAINT FK_DOC_M FOREIGN KEY (materiel_id) REFERENCES materiel (id) ON DELETE CASCADE,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS document');
        $this->addSql('DROP TABLE IF EXISTS alerte');
        $this->addSql('DROP TABLE IF EXISTS evenement');
        $this->addSql('DROP TABLE IF EXISTS reservation');
        $this->addSql('DROP TABLE IF EXISTS emprunt');
        $this->addSql('DROP TABLE IF EXISTS materiel');
        $this->addSql('DROP TABLE IF EXISTS profil_categorie');
        $this->addSql('DROP TABLE IF EXISTS utilisateur');
        $this->addSql('DROP TABLE IF EXISTS categorie');
        $this->addSql('DROP TABLE IF EXISTS profil');
    }
}
