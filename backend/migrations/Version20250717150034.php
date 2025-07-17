<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717150034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE candidature (id SERIAL NOT NULL, utilisateur_id INT NOT NULL, titre_poste VARCHAR(255) NOT NULL, entreprise VARCHAR(255) NOT NULL, statut VARCHAR(50) NOT NULL, date_depot DATE NOT NULL, date_entretien DATE DEFAULT NULL, notes TEXT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B8FB88E14F ON candidature (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP CONSTRAINT FK_E33BD3B8FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidature
        SQL);
    }
}
