<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428220236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE blacklisted_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blacklisted_token (id INT NOT NULL, token TEXT NOT NULL, blacklisted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_27D936645F37A13B ON blacklisted_token (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id INT NOT NULL, report_id INT DEFAULT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C4BD2A4C0 ON comment (report_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report (id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN report.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE blacklisted_token_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE comment_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE refresh_tokens_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE report_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE "user_id_seq" CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C4BD2A4C0
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blacklisted_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
