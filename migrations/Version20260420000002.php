<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index MySQL pour performances';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_emprunt_statut ON emprunt (statut)');
        $this->addSql('CREATE INDEX idx_emprunt_date ON emprunt (statut, date_fin_prevue)');
        $this->addSql('CREATE INDEX idx_materiel_etat ON materiel (etat)');
        $this->addSql('CREATE INDEX idx_alerte_lu ON alerte (lu)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_emprunt_statut ON emprunt');
        $this->addSql('DROP INDEX idx_emprunt_date ON emprunt');
        $this->addSql('DROP INDEX idx_materiel_etat ON materiel');
        $this->addSql('DROP INDEX idx_alerte_lu ON alerte');
    }
}
