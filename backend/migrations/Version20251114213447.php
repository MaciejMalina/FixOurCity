<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114213447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD user_id INT NOT NULL DEFAULT 1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F7784A76ED395 ON report (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD approved_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD approved BOOLEAN DEFAULT false NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6492D234F6A FOREIGN KEY (approved_by_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D6492D234F6A ON "user" (approved_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_approved_idx ON "user" (approved)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F7784A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C42F7784A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6492D234F6A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D6492D234F6A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_approved_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP approved_by_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP approved
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP approved_at
        SQL);
    }
}
