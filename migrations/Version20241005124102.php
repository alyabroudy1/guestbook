<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005124102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('DROP SEQUENCE messenger_messages_id_seq CASCADE');
//        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F1F47645AE ON link (url)');
        $this->addSql('ALTER TABLE movie ADD url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD tvg_name VARCHAR(70) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD tvg_logo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD group_title VARCHAR(70) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE messenger_messages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
        $this->addSql('DROP INDEX UNIQ_36AC99F1F47645AE');
        $this->addSql('ALTER TABLE movie DROP url');
        $this->addSql('ALTER TABLE movie DROP tvg_name');
        $this->addSql('ALTER TABLE movie DROP tvg_logo');
        $this->addSql('ALTER TABLE movie DROP group_title');
    }
}
