<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260004000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reservation : datetime → date pour date_debut et date_fin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation CHANGE date_debut date_debut DATE NOT NULL');
        $this->addSql('ALTER TABLE reservation CHANGE date_fin date_fin DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation CHANGE date_debut date_debut DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reservation CHANGE date_fin date_fin DATETIME NOT NULL');
    }
}
