<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511141640 extends AbstractMigration
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
            CREATE TABLE categories (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3AF346685E237E06 ON categories (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX category_name_idx ON categories (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comments (id SERIAL NOT NULL, user_id INT NOT NULL, report_id INT NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5F9E962AA76ED395 ON comments (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5F9E962A4BD2A4C0 ON comments (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX comment_created_idx ON comments (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comments.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE images (id SERIAL NOT NULL, report_id INT NOT NULL, url VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E01FBE6A4BD2A4C0 ON images (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report (id SERIAL NOT NULL, user_id INT DEFAULT NULL, status_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F7784A76ED395 ON report (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F77846BF700BD ON report (status_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F778412469DE2 ON report (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX created_at_idx ON report (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN report.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report_tag (report_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(report_id, tag_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F372CF044BD2A4C0 ON report_tag (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F372CF04BAD26311 ON report_tag (tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE statuses (id SERIAL NOT NULL, label VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4BF01E11EA750E8 ON statuses (label)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX status_label_idx ON statuses (label)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tags (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6FBC94265E237E06 ON tags (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX tag_name_idx ON tags (name)
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
            ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F77846BF700BD FOREIGN KEY (status_id) REFERENCES statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F778412469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_tag ADD CONSTRAINT FK_F372CF044BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_tag ADD CONSTRAINT FK_F372CF04BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
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
            ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images DROP CONSTRAINT FK_E01FBE6A4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F7784A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F77846BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F778412469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_tag DROP CONSTRAINT FK_F372CF044BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_tag DROP CONSTRAINT FK_F372CF04BAD26311
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
            DROP TABLE categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE images
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE statuses
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report_follower
        SQL);
    }
}
