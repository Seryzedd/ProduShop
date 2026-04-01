<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401100826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shelf_translation DROP FOREIGN KEY `FK_5C15A7932C2AC5D3`');
        $this->addSql('ALTER TABLE shelf_translation ADD CONSTRAINT FK_5C15A7932C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES shelf (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shelf_translation DROP FOREIGN KEY FK_5C15A7932C2AC5D3');
        $this->addSql('ALTER TABLE shelf_translation ADD CONSTRAINT `FK_5C15A7932C2AC5D3` FOREIGN KEY (translatable_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
