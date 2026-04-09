<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260003000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'index sur emprunt.statut, emprunt.date_fin_prevue, materiel.etat, alerte.lu';
    }

    public function up(Schema $schema): void
    {
        // Index sur emprunt.statut — filtré dans findEnCours(), findRetards(), findWithFilters(), countRetards()
        $this->addSql('CREATE INDEX IDX_EMPRUNT_STATUT ON emprunt (statut)');

        // Index sur emprunt.date_fin_prevue — utilisé dans countRetards() (WHERE date_fin_prevue < now)
        // et dans les tris orderBy(dateFinPrevue)
        $this->addSql('CREATE INDEX IDX_EMPRUNT_DATE_FIN ON emprunt (date_fin_prevue)');

        // Index composite (statut, date_fin_prevue) — optimise countRetards() qui filtre sur les deux
        $this->addSql('CREATE INDEX IDX_EMPRUNT_STATUT_FIN ON emprunt (statut, date_fin_prevue)');

        // Index sur materiel.etat — filtré dans findWithFilters() et countDisponibles()
        $this->addSql('CREATE INDEX IDX_MATERIEL_ETAT ON materiel (etat)');

        // Index sur alerte.lu — filtré dans findNonLues() et countNonLues()
        $this->addSql('CREATE INDEX IDX_ALERTE_LU ON alerte (lu)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_EMPRUNT_STATUT ON emprunt');
        $this->addSql('DROP INDEX IDX_EMPRUNT_DATE_FIN ON emprunt');
        $this->addSql('DROP INDEX IDX_EMPRUNT_STATUT_FIN ON emprunt');
        $this->addSql('DROP INDEX IDX_MATERIEL_ETAT ON materiel');
        $this->addSql('DROP INDEX IDX_ALERTE_LU ON alerte');
    }
}
