<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260214102042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, src LONGTEXT NOT NULL, extension VARCHAR(10) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_D34A04AD3DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE slide_item (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, slider_id INT NOT NULL, UNIQUE INDEX UNIQ_28DBCBCA3DA5256D (image_id), INDEX IDX_28DBCBCA2CCC9638 (slider_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE slider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT NOT NULL, product_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CFC710074584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES abstract_user (id)');
        $this->addSql('ALTER TABLE slide_item ADD CONSTRAINT FK_28DBCBCA3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE slide_item ADD CONSTRAINT FK_28DBCBCA2CCC9638 FOREIGN KEY (slider_id) REFERENCES slider (id)');
        $this->addSql('ALTER TABLE slider ADD CONSTRAINT FK_CFC710074584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3DA5256D');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE slide_item DROP FOREIGN KEY FK_28DBCBCA3DA5256D');
        $this->addSql('ALTER TABLE slide_item DROP FOREIGN KEY FK_28DBCBCA2CCC9638');
        $this->addSql('ALTER TABLE slider DROP FOREIGN KEY FK_CFC710074584665A');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE slide_item');
        $this->addSql('DROP TABLE slider');
    }
}
