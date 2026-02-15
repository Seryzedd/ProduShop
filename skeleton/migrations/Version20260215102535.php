<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215102535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, stock INT NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, taxe DOUBLE PRECISION NOT NULL, product_id INT NOT NULL, INDEX IDX_DE6867954584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE6867954584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE package DROP FOREIGN KEY FK_DE6867954584665A');
        $this->addSql('DROP TABLE package');
    }
}
