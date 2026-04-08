<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406162542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE text_translation (content LONGTEXT NOT NULL, id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_A42FC53C2C2AC5D3 (translatable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE text_translation ADD CONSTRAINT FK_A42FC53C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES abstract_text (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE text_translation DROP FOREIGN KEY FK_A42FC53C2C2AC5D3');
        $this->addSql('DROP TABLE text_translation');
    }
}
