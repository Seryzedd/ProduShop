<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260909161141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seo_keyword (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_F3AA4B70A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE seo_keyword ADD CONSTRAINT FK_F3AA4B70A76ED395 FOREIGN KEY (user_id) REFERENCES abstract_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo_keyword DROP FOREIGN KEY FK_F3AA4B70A76ED395');
        $this->addSql('DROP TABLE seo_keyword');
    }
}
