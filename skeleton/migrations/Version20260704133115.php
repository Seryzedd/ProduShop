<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704133115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `condition` (id INT AUTO_INCREMENT NOT NULL, operator VARCHAR(10) NOT NULL, field VARCHAR(255) NOT NULL, alias VARCHAR(100) NOT NULL, extractor_id INT NOT NULL, INDEX IDX_BDD68843DBCA8D53 (extractor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE selector (id INT AUTO_INCREMENT NOT NULL, property JSON NOT NULL, type VARCHAR(50) NOT NULL, sql_generator_id INT NOT NULL, INDEX IDX_9692E25D99A0F4AD (sql_generator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE sql_generator (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, entityclass_name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE `condition` ADD CONSTRAINT FK_BDD68843DBCA8D53 FOREIGN KEY (extractor_id) REFERENCES sql_generator (id)');
        $this->addSql('ALTER TABLE selector ADD CONSTRAINT FK_9692E25D99A0F4AD FOREIGN KEY (sql_generator_id) REFERENCES sql_generator (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `condition` DROP FOREIGN KEY FK_BDD68843DBCA8D53');
        $this->addSql('ALTER TABLE selector DROP FOREIGN KEY FK_9692E25D99A0F4AD');
        $this->addSql('DROP TABLE `condition`');
        $this->addSql('DROP TABLE selector');
        $this->addSql('DROP TABLE sql_generator');
    }
}
