<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260911142206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seo_professional_informations (id INT AUTO_INCREMENT NOT NULL, meta LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, logo_tag VARCHAR(255) NOT NULL, webpage_title VARCHAR(255) NOT NULL, professional_id INT NOT NULL, UNIQUE INDEX UNIQ_D4985ABCDB77003 (professional_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE seo_professional_informations ADD CONSTRAINT FK_D4985ABCDB77003 FOREIGN KEY (professional_id) REFERENCES professional (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo_professional_informations DROP FOREIGN KEY FK_D4985ABCDB77003');
        $this->addSql('DROP TABLE seo_professional_informations');
    }
}
