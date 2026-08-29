<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260827153610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE documentation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT NOT NULL, category_id INT NOT NULL, INDEX IDX_73D5A93B12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE step (id INT AUTO_INCREMENT NOT NULL, position INT NOT NULL, title VARCHAR(255) DEFAULT NULL, text LONGTEXT NOT NULL, documentation_id INT NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_43B9FE3CC703EEC9 (documentation_id), UNIQUE INDEX UNIQ_43B9FE3C3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE documentation ADD CONSTRAINT FK_73D5A93B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CC703EEC9 FOREIGN KEY (documentation_id) REFERENCES documentation (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documentation DROP FOREIGN KEY FK_73D5A93B12469DE2');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3CC703EEC9');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C3DA5256D');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE documentation');
        $this->addSql('DROP TABLE step');
    }
}
