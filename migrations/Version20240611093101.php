<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611093101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE link_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE server_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE link (id INT NOT NULL, server_id INT DEFAULT NULL, movie_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, splittable BOOLEAN DEFAULT NULL, authority VARCHAR(70) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_36AC99F11844E6B7 ON link (server_id)');
        $this->addSql('CREATE INDEX IDX_36AC99F18F93B6FC ON link (movie_id)');
        $this->addSql('CREATE TABLE movie (id INT NOT NULL, series_id INT DEFAULT NULL, season_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, card_image VARCHAR(255) DEFAULT NULL, background_image VARCHAR(255) DEFAULT NULL, rate VARCHAR(50) DEFAULT NULL, played_time INT DEFAULT NULL, total_time INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, search_context VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1D5EF26F5278319C ON movie (series_id)');
        $this->addSql('CREATE INDEX IDX_1D5EF26F4EC001D1 ON movie (season_id)');
        $this->addSql('COMMENT ON COLUMN movie.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN movie.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE movie_category (movie_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(movie_id, category_id))');
        $this->addSql('CREATE INDEX IDX_DABA824C8F93B6FC ON movie_category (movie_id)');
        $this->addSql('CREATE INDEX IDX_DABA824C12469DE2 ON movie_category (category_id)');
        $this->addSql('CREATE TABLE server (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, headers JSON DEFAULT NULL, cookie TEXT DEFAULT NULL, authority VARCHAR(255) DEFAULT NULL, rate SMALLINT DEFAULT NULL, active BOOLEAN DEFAULT NULL, default_authority VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A6DD5F6D79572D9 ON server (model)');
//        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
//        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
//        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
//        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
//        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
//            BEGIN
//                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
//                RETURN NEW;
//            END;
//        $$ LANGUAGE plpgsql;');
//        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
//        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F11844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F18F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F5278319C FOREIGN KEY (series_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F4EC001D1 FOREIGN KEY (season_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_category ADD CONSTRAINT FK_DABA824C8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_category ADD CONSTRAINT FK_DABA824C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE link_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE server_id_seq CASCADE');
        $this->addSql('ALTER TABLE link DROP CONSTRAINT FK_36AC99F11844E6B7');
        $this->addSql('ALTER TABLE link DROP CONSTRAINT FK_36AC99F18F93B6FC');
        $this->addSql('ALTER TABLE movie DROP CONSTRAINT FK_1D5EF26F5278319C');
        $this->addSql('ALTER TABLE movie DROP CONSTRAINT FK_1D5EF26F4EC001D1');
        $this->addSql('ALTER TABLE movie_category DROP CONSTRAINT FK_DABA824C8F93B6FC');
        $this->addSql('ALTER TABLE movie_category DROP CONSTRAINT FK_DABA824C12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE link');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_category');
        $this->addSql('DROP TABLE server');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
