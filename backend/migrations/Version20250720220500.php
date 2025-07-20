<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour changer le type de la colonne heure_entretien de TIME vers VARCHAR
 */
final class Version20250720220500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change heure_entretien column type from TIME to VARCHAR(8)';
    }

    public function up(Schema $schema): void
    {
        // Modifier le type de la colonne heure_entretien pour PostgreSQL
        $this->addSql('ALTER TABLE candidature ALTER COLUMN heure_entretien TYPE VARCHAR(8)');
    }

    public function down(Schema $schema): void
    {
        // Revenir au type TIME pour PostgreSQL
        $this->addSql('ALTER TABLE candidature ALTER COLUMN heure_entretien TYPE TIME');
    }
}
