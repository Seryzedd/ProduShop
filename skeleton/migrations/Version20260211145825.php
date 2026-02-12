<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211145825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY `FK_5CECC7BEC273A89B`');
        $this->addSql('DROP INDEX IDX_5CECC7BEC273A89B ON adress');
        $this->addSql('ALTER TABLE adress ADD zip_code VARCHAR(255) NOT NULL, CHANGE shipping_adress_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT FK_5CECC7BEA76ED395 FOREIGN KEY (user_id) REFERENCES abstract_user (id)');
        $this->addSql('CREATE INDEX IDX_5CECC7BEA76ED395 ON adress (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY FK_5CECC7BEA76ED395');
        $this->addSql('DROP INDEX IDX_5CECC7BEA76ED395 ON adress');
        $this->addSql('ALTER TABLE adress DROP zip_code, CHANGE user_id shipping_adress_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT `FK_5CECC7BEC273A89B` FOREIGN KEY (shipping_adress_id) REFERENCES abstract_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5CECC7BEC273A89B ON adress (shipping_adress_id)');
    }
}
