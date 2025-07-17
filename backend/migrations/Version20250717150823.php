<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717150823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE candidature_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP CONSTRAINT fk_e33bd3b8fb88e14f
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP CONSTRAINT fk_a45bddc1be04ea9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_a45bddc1be04ea9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD titre_poste VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD entreprise VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD date_depot DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD date_entretien DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP job_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP applied_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP resume_path
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application RENAME COLUMN status TO statut
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application RENAME COLUMN cover_letter TO notes
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE candidature_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE candidature (id SERIAL NOT NULL, utilisateur_id INT NOT NULL, titre_poste VARCHAR(255) NOT NULL, entreprise VARCHAR(255) NOT NULL, statut VARCHAR(50) NOT NULL, date_depot DATE NOT NULL, date_entretien DATE DEFAULT NULL, notes TEXT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_e33bd3b8fb88e14f ON candidature (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_e33bd3b8fb88e14f FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD job_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD resume_path VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP titre_poste
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP entreprise
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP date_depot
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP date_entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application RENAME COLUMN date_creation TO applied_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application RENAME COLUMN notes TO cover_letter
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application RENAME COLUMN statut TO status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD CONSTRAINT fk_a45bddc1be04ea9 FOREIGN KEY (job_id) REFERENCES job (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_a45bddc1be04ea9 ON application (job_id)
        SQL);
    }
}
