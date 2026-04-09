<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260002000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout parent_id sur emprunt (accessoires groupés) + FK sur materiel_accessoire';
    }

    public function up(Schema $schema): void
    {
        // Colonne parent_id pour lier les emprunts d'accessoires à l'emprunt principal
        $this->addSql('ALTER TABLE emprunt ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_EMPRUNT_PARENT FOREIGN KEY (parent_id) REFERENCES emprunt (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_EMPRUNT_PARENT ON emprunt (parent_id)');

        // FK sur materiel_accessoire si elles n'existent pas encore
        $this->addSql('ALTER TABLE materiel_accessoire
            ADD CONSTRAINT FK_MA_MATERIEL FOREIGN KEY (materiel_id) REFERENCES materiel (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_MA_ACCESSOIRE FOREIGN KEY (accessoire_id) REFERENCES materiel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_EMPRUNT_PARENT');
        $this->addSql('DROP INDEX IDX_EMPRUNT_PARENT ON emprunt');
        $this->addSql('ALTER TABLE emprunt DROP COLUMN parent_id');
        $this->addSql('ALTER TABLE materiel_accessoire DROP FOREIGN KEY FK_MA_MATERIEL');
        $this->addSql('ALTER TABLE materiel_accessoire DROP FOREIGN KEY FK_MA_ACCESSOIRE');
    }
}
