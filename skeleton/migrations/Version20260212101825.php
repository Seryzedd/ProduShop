<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212101825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (gender VARCHAR(10) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE professional (siret VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, adress_id INT NOT NULL, id INT NOT NULL, UNIQUE INDEX UNIQ_B3B573AA8486F9AC (adress_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES abstract_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AA8486F9AC FOREIGN KEY (adress_id) REFERENCES adress (id)');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AABF396750 FOREIGN KEY (id) REFERENCES abstract_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE abstract_user DROP FOREIGN KEY `FK_7E77A5488486F9AC`');
        $this->addSql('DROP INDEX IDX_7E77A5488486F9AC ON abstract_user');
        $this->addSql('ALTER TABLE abstract_user DROP firstname, DROP lastname, DROP siret, DROP company_name, DROP adress_id, DROP gender');
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY `FK_5CECC7BEA76ED395`');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT FK_5CECC7BEA76ED395 FOREIGN KEY (user_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455BF396750');
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AA8486F9AC');
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AABF396750');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE professional');
        $this->addSql('ALTER TABLE abstract_user ADD firstname VARCHAR(255) DEFAULT NULL, ADD lastname VARCHAR(255) DEFAULT NULL, ADD siret VARCHAR(255) DEFAULT NULL, ADD company_name VARCHAR(255) DEFAULT NULL, ADD adress_id INT NOT NULL, ADD gender VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_user ADD CONSTRAINT `FK_7E77A5488486F9AC` FOREIGN KEY (adress_id) REFERENCES adress (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7E77A5488486F9AC ON abstract_user (adress_id)');
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY FK_5CECC7BEA76ED395');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT `FK_5CECC7BEA76ED395` FOREIGN KEY (user_id) REFERENCES abstract_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
