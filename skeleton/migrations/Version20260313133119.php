<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313133119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hours (id INT AUTO_INCREMENT NOT NULL, start_hour INT NOT NULL, start_minutes INT NOT NULL, end_hour INT NOT NULL, end_minutes INT NOT NULL, day_id INT NOT NULL, INDEX IDX_8A1ABD8D9C24126 (day_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE opening_schedule (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B7B4F875A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE schedule_day (id INT AUTO_INCREMENT NOT NULL, day INT NOT NULL, opening_schedule_id INT NOT NULL, INDEX IDX_78696C9A2412731C (opening_schedule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE hours ADD CONSTRAINT FK_8A1ABD8D9C24126 FOREIGN KEY (day_id) REFERENCES schedule_day (id)');
        $this->addSql('ALTER TABLE opening_schedule ADD CONSTRAINT FK_B7B4F875A76ED395 FOREIGN KEY (user_id) REFERENCES professional (id)');
        $this->addSql('ALTER TABLE schedule_day ADD CONSTRAINT FK_78696C9A2412731C FOREIGN KEY (opening_schedule_id) REFERENCES opening_schedule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hours DROP FOREIGN KEY FK_8A1ABD8D9C24126');
        $this->addSql('ALTER TABLE opening_schedule DROP FOREIGN KEY FK_B7B4F875A76ED395');
        $this->addSql('ALTER TABLE schedule_day DROP FOREIGN KEY FK_78696C9A2412731C');
        $this->addSql('DROP TABLE hours');
        $this->addSql('DROP TABLE opening_schedule');
        $this->addSql('DROP TABLE schedule_day');
    }
}
