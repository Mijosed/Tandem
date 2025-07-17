<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717153747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event DROP CONSTRAINT fk_c7f7cafb3e030acd
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE application_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE candidature (id SERIAL NOT NULL, user_id INT NOT NULL, titre_poste VARCHAR(255) NOT NULL, entreprise VARCHAR(255) NOT NULL, statut VARCHAR(50) NOT NULL, date_depot DATE NOT NULL, date_entretien DATE DEFAULT NULL, notes TEXT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B8A76ED395 ON candidature (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP CONSTRAINT fk_a45bddc1a76ed395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE application
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_c7f7cafb3e030acd
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event RENAME COLUMN application_id TO candidature_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event ADD CONSTRAINT FK_C7F7CAFBB6121583 FOREIGN KEY (candidature_id) REFERENCES candidature (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7F7CAFBB6121583 ON schedule_event (candidature_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event DROP CONSTRAINT FK_C7F7CAFBB6121583
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE application_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE application (id SERIAL NOT NULL, user_id INT NOT NULL, notes TEXT DEFAULT NULL, statut VARCHAR(50) NOT NULL, titre_poste VARCHAR(255) NOT NULL, entreprise VARCHAR(255) NOT NULL, date_depot DATE NOT NULL, date_entretien DATE DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_a45bddc1a76ed395 ON application (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD CONSTRAINT fk_a45bddc1a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP CONSTRAINT FK_E33BD3B8A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C7F7CAFBB6121583
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event RENAME COLUMN candidature_id TO application_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event ADD CONSTRAINT fk_c7f7cafb3e030acd FOREIGN KEY (application_id) REFERENCES application (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_c7f7cafb3e030acd ON schedule_event (application_id)
        SQL);
    }
}
