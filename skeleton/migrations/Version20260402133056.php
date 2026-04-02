<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402133056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE professional_translation (description TINYTEXT NOT NULL, id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_6E3233F2C2AC5D3 (translatable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE professional_translation ADD CONSTRAINT FK_6E3233F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES professional (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional_translation DROP FOREIGN KEY FK_6E3233F2C2AC5D3');
        $this->addSql('DROP TABLE professional_translation');
    }
}
