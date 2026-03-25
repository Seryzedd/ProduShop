<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325121344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block ADD background_color_id INT DEFAULT NULL, DROP background_color');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A1A51272 FOREIGN KEY (background_color_id) REFERENCES picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722A1A51272 ON block (background_color_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A1A51272');
        $this->addSql('DROP INDEX UNIQ_831B9722A1A51272 ON block');
        $this->addSql('ALTER TABLE block ADD background_color VARCHAR(50) DEFAULT NULL, DROP background_color_id');
    }
}
