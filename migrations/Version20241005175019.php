<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005175019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link DROP CONSTRAINT fk_36ac99f18f93b6fc');
        $this->addSql('DROP INDEX idx_36ac99f18f93b6fc');
        $this->addSql('ALTER TABLE link DROP movie_id');
        $this->addSql('ALTER TABLE movie ADD link_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26FADA40271 FOREIGN KEY (link_id) REFERENCES link (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1D5EF26FADA40271 ON movie (link_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE link ADD movie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT fk_36ac99f18f93b6fc FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_36ac99f18f93b6fc ON link (movie_id)');
        $this->addSql('ALTER TABLE movie DROP CONSTRAINT FK_1D5EF26FADA40271');
        $this->addSql('DROP INDEX IDX_1D5EF26FADA40271');
        $this->addSql('ALTER TABLE movie DROP link_id');
    }
}
