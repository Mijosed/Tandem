<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration corrective pour le 2FA - Assure que two_factor_enabled a une valeur par défaut
 */
final class Version20250122000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix two_factor_enabled default value for production compatibility';
    }

    public function up(Schema $schema): void
    {
        
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" 
            ALTER COLUMN two_factor_enabled SET DEFAULT FALSE
        SQL);
        
        $this->addSql(<<<'SQL'
            UPDATE "user" 
            SET two_factor_enabled = FALSE 
            WHERE two_factor_enabled IS NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" 
            ALTER COLUMN two_factor_enabled DROP DEFAULT
        SQL);
    }
} 