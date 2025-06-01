<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601103658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE audit_logs (id SERIAL NOT NULL, user_id INT NOT NULL, action VARCHAR(100) NOT NULL, target_type VARCHAR(50) NOT NULL, target_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D62F2858A76ED395 ON audit_logs (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX audit_created_idx ON audit_logs (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX audit_target_idx ON audit_logs (target_type, target_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN audit_logs.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blacklisted_tokens (id SERIAL NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(512) NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_51F6B37C5F37A13B ON blacklisted_tokens (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_51F6B37CA76ED395 ON blacklisted_tokens (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX token_idx ON blacklisted_tokens (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX expired_idx ON blacklisted_tokens (expired_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN blacklisted_tokens.expired_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN blacklisted_tokens.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id SERIAL NOT NULL, report_id INT NOT NULL, content TEXT NOT NULL, author VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C4BD2A4C0 ON comment (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE image (id SERIAL NOT NULL, report_id INT NOT NULL, url TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C53D045F4BD2A4C0 ON image (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_tokens (id SERIAL NOT NULL, user_id INT NOT NULL, token VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9BACE7E15F37A13B ON refresh_tokens (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN refresh_tokens.expires_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report (id SERIAL NOT NULL, category_id INT NOT NULL, status_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F778412469DE2 ON report (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F77846BF700BD ON report (status_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE status (id SERIAL NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_7B00651CEA750E8 ON status (label)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX email_idx ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report_follower (user_id INT NOT NULL, report_id INT NOT NULL, PRIMARY KEY(user_id, report_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B9B6903BA76ED395 ON report_follower (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B9B6903B4BD2A4C0 ON report_follower (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE audit_logs ADD CONSTRAINT FK_D62F2858A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blacklisted_tokens ADD CONSTRAINT FK_51F6B37CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image ADD CONSTRAINT FK_C53D045F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_tokens ADD CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F778412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F77846BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_follower ADD CONSTRAINT FK_B9B6903BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_follower ADD CONSTRAINT FK_B9B6903B4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE audit_logs DROP CONSTRAINT FK_D62F2858A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blacklisted_tokens DROP CONSTRAINT FK_51F6B37CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image DROP CONSTRAINT FK_C53D045F4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_tokens DROP CONSTRAINT FK_9BACE7E1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F778412469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F77846BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_follower DROP CONSTRAINT FK_B9B6903BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_follower DROP CONSTRAINT FK_B9B6903B4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE audit_logs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blacklisted_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE status
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report_follower
        SQL);
    }
}
