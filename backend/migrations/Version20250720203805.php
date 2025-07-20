<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720203805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ALTER heure_entretien TYPE VARCHAR(10)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP CONSTRAINT fk_bf5476cab6121583
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_bf5476cab6121583
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP candidature_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP interview_time
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ALTER heure_entretien TYPE VARCHAR(8)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD candidature_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD interview_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT fk_bf5476cab6121583 FOREIGN KEY (candidature_id) REFERENCES candidature (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_bf5476cab6121583 ON notification (candidature_id)
        SQL);
    }
}
