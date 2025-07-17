<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717111036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE application (id SERIAL NOT NULL, job_id INT NOT NULL, user_id INT NOT NULL, applied_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cover_letter TEXT DEFAULT NULL, resume_path VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A45BDDC1BE04EA9 ON application (job_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A45BDDC1A76ED395 ON application (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE job (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, contract_type VARCHAR(100) NOT NULL, salary_min INT DEFAULT NULL, salary_max INT DEFAULT NULL, description TEXT NOT NULL, indeed_job_key VARCHAR(255) DEFAULT NULL, is_premium BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id SERIAL NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, type VARCHAR(50) NOT NULL, is_read BOOLEAN NOT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id SERIAL NOT NULL, subscription_id INT NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(100) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6D28840D9A1887DC ON payment (subscription_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE schedule_event (id SERIAL NOT NULL, user_id INT NOT NULL, application_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(100) NOT NULL, location VARCHAR(255) DEFAULT NULL, all_day BOOLEAN NOT NULL, color VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7F7CAFBA76ED395 ON schedule_event (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7F7CAFB3E030ACD ON schedule_event (application_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription (id SERIAL NOT NULL, user_id INT NOT NULL, plan VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, stripe_subscription_id VARCHAR(255) DEFAULT NULL, current_period_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, current_period_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_A3C664D3A76ED395 ON subscription (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event ADD CONSTRAINT FK_C7F7CAFBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event ADD CONSTRAINT FK_C7F7CAFB3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1BE04EA9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP CONSTRAINT FK_6D28840D9A1887DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event DROP CONSTRAINT FK_C7F7CAFBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule_event DROP CONSTRAINT FK_C7F7CAFB3E030ACD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE application
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE job
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE schedule_event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
