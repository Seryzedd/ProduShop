<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325121613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY `FK_831B9722A1A51272`');
        $this->addSql('DROP INDEX UNIQ_831B9722A1A51272 ON block');
        $this->addSql('ALTER TABLE block CHANGE background_image background_color LONGTEXT DEFAULT NULL, CHANGE background_color_id background_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722E6DA28AA FOREIGN KEY (background_image_id) REFERENCES picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722E6DA28AA ON block (background_image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722E6DA28AA');
        $this->addSql('DROP INDEX UNIQ_831B9722E6DA28AA ON block');
        $this->addSql('ALTER TABLE block CHANGE background_color background_image LONGTEXT DEFAULT NULL, CHANGE background_image_id background_color_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT `FK_831B9722A1A51272` FOREIGN KEY (background_color_id) REFERENCES picture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722A1A51272 ON block (background_color_id)');
    }
}
