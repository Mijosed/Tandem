<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification (
            id SERIAL NOT NULL,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            read BOOLEAN NOT NULL DEFAULT false,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            data JSON DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CAA76ED395');
        $this->addSql('DROP TABLE notification');
    }
} 