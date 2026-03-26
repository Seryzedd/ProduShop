<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326092733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_text ADD block_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_text ADD CONSTRAINT FK_C86FD4C6E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('CREATE INDEX IDX_C86FD4C6E9ED820C ON abstract_text (block_id)');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY `FK_831B9722DAF346C4`');
        $this->addSql('DROP INDEX UNIQ_831B9722DAF346C4 ON block');
        $this->addSql('ALTER TABLE block DROP text_color, DROP html_element_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_text DROP FOREIGN KEY FK_C86FD4C6E9ED820C');
        $this->addSql('DROP INDEX IDX_C86FD4C6E9ED820C ON abstract_text');
        $this->addSql('ALTER TABLE abstract_text DROP block_id');
        $this->addSql('ALTER TABLE block ADD text_color VARCHAR(50) NOT NULL, ADD html_element_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT `FK_831B9722DAF346C4` FOREIGN KEY (html_element_id) REFERENCES abstract_text (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722DAF346C4 ON block (html_element_id)');
    }
}
