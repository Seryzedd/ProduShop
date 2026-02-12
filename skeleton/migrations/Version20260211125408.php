<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211125408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_user ADD adress_id INT NOT NULL');
        $this->addSql('ALTER TABLE abstract_user ADD CONSTRAINT FK_7E77A5488486F9AC FOREIGN KEY (adress_id) REFERENCES adress (id)');
        $this->addSql('CREATE INDEX IDX_7E77A5488486F9AC ON abstract_user (adress_id)');
        $this->addSql('ALTER TABLE adress ADD shipping_adress_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT FK_5CECC7BEC273A89B FOREIGN KEY (shipping_adress_id) REFERENCES abstract_user (id)');
        $this->addSql('CREATE INDEX IDX_5CECC7BEC273A89B ON adress (shipping_adress_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_user DROP FOREIGN KEY FK_7E77A5488486F9AC');
        $this->addSql('DROP INDEX IDX_7E77A5488486F9AC ON abstract_user');
        $this->addSql('ALTER TABLE abstract_user DROP adress_id');
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY FK_5CECC7BEC273A89B');
        $this->addSql('DROP INDEX IDX_5CECC7BEC273A89B ON adress');
        $this->addSql('ALTER TABLE adress DROP shipping_adress_id');
    }
}
