<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223154852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional ADD logo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AAF98F144A FOREIGN KEY (logo_id) REFERENCES picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3B573AAF98F144A ON professional (logo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AAF98F144A');
        $this->addSql('DROP INDEX UNIQ_B3B573AAF98F144A ON professional');
        $this->addSql('ALTER TABLE professional DROP logo_id');
    }
}
