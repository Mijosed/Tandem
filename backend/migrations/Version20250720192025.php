<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720192025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD two_factor_secret VARCHAR(32) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD two_factor_enabled BOOLEAN NOT NULL DEFAULT FALSE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD backup_codes JSON DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP two_factor_secret
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP two_factor_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP backup_codes
        SQL);
    }
}
