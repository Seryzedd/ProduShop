<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325164323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abstract_text (id INT AUTO_INCREMENT NOT NULL, color VARCHAR(255) NOT NULL, align VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, classes JSON NOT NULL, text_type VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE link (target VARCHAR(255) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE little_title (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE main_title (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE normal_title (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE paragraph (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE sub_title (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F1BF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE little_title ADD CONSTRAINT FK_61FF1B82BF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE main_title ADD CONSTRAINT FK_886ACB2DBF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE normal_title ADD CONSTRAINT FK_66919B31BF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862BF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_title ADD CONSTRAINT FK_C7721CD2BF396750 FOREIGN KEY (id) REFERENCES abstract_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block ADD html_element_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722DAF346C4 FOREIGN KEY (html_element_id) REFERENCES abstract_text (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722DAF346C4 ON block (html_element_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link DROP FOREIGN KEY FK_36AC99F1BF396750');
        $this->addSql('ALTER TABLE little_title DROP FOREIGN KEY FK_61FF1B82BF396750');
        $this->addSql('ALTER TABLE main_title DROP FOREIGN KEY FK_886ACB2DBF396750');
        $this->addSql('ALTER TABLE normal_title DROP FOREIGN KEY FK_66919B31BF396750');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862BF396750');
        $this->addSql('ALTER TABLE sub_title DROP FOREIGN KEY FK_C7721CD2BF396750');
        $this->addSql('DROP TABLE abstract_text');
        $this->addSql('DROP TABLE link');
        $this->addSql('DROP TABLE little_title');
        $this->addSql('DROP TABLE main_title');
        $this->addSql('DROP TABLE normal_title');
        $this->addSql('DROP TABLE paragraph');
        $this->addSql('DROP TABLE sub_title');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722DAF346C4');
        $this->addSql('DROP INDEX UNIQ_831B9722DAF346C4 ON block');
        $this->addSql('ALTER TABLE block DROP html_element_id');
    }
}
