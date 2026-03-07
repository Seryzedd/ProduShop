<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307095420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stripe_customer (id INT AUTO_INCREMENT NOT NULL, customer_id LONGTEXT NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_DC7E523AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE stripe_customer ADD CONSTRAINT FK_DC7E523AA76ED395 FOREIGN KEY (user_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stripe_customer DROP FOREIGN KEY FK_DC7E523AA76ED395');
        $this->addSql('DROP TABLE stripe_customer');
    }
}
