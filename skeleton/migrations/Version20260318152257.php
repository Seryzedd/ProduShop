<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318152257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abstract_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, phone VARCHAR(100) NOT NULL, user_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE adress (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(100) NOT NULL, country VARCHAR(255) NOT NULL, complement VARCHAR(255) NOT NULL, zip_code VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_5CECC7BEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE client (gender VARCHAR(10) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE hours (id INT AUTO_INCREMENT NOT NULL, start_hour INT NOT NULL, start_minutes INT NOT NULL, end_hour INT NOT NULL, end_minutes INT NOT NULL, day_id INT NOT NULL, INDEX IDX_8A1ABD8D9C24126 (day_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE opening_schedule (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B7B4F875A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, intent_id VARCHAR(255) NOT NULL, amount INT NOT NULL, currency VARCHAR(15) NOT NULL, status VARCHAR(50) NOT NULL, paid_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, package_id INT NOT NULL, purchase_id INT NOT NULL, merchant_id INT DEFAULT NULL, INDEX IDX_52EA1F09F44CABFF (package_id), INDEX IDX_52EA1F09558FBEB9 (purchase_id), INDEX IDX_52EA1F096796D554 (merchant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, stock INT NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, taxe DOUBLE PRECISION NOT NULL, name VARCHAR(255) NOT NULL, product_id INT NOT NULL, INDEX IDX_DE6867954584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, src LONGTEXT NOT NULL, extension VARCHAR(10) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image_id INT DEFAULT NULL, company_id INT NOT NULL, shelf_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_D34A04AD3DA5256D (image_id), INDEX IDX_D34A04AD979B1AD6 (company_id), INDEX IDX_D34A04AD7C12FBC0 (shelf_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE professional (siret VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, adress_id INT NOT NULL, logo_id INT DEFAULT NULL, id INT NOT NULL, UNIQUE INDEX UNIQ_B3B573AA8486F9AC (adress_id), UNIQUE INDEX UNIQ_B3B573AAF98F144A (logo_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE schedule_day (id INT AUTO_INCREMENT NOT NULL, day INT NOT NULL, opening_schedule_id INT NOT NULL, INDEX IDX_78696C9A2412731C (opening_schedule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE shelf (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE slide_item (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, slider_id INT NOT NULL, UNIQUE INDEX UNIQ_28DBCBCA3DA5256D (image_id), INDEX IDX_28DBCBCA2CCC9638 (slider_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE slider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT NOT NULL, product_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CFC710074584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE stripe (id INT AUTO_INCREMENT NOT NULL, authentication_key LONGTEXT NOT NULL, public_key LONGTEXT NOT NULL, secret_key LONGTEXT NOT NULL, active TINYINT NOT NULL, fees_amount DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE stripe_customer (id INT AUTO_INCREMENT NOT NULL, customer_id LONGTEXT NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_DC7E523AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE stripe_merchant (id INT AUTO_INCREMENT NOT NULL, account_id LONGTEXT NOT NULL, created_at DATETIME NOT NULL, ready TINYINT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_29ECF9D2A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, transfer_id VARCHAR(255) NOT NULL, charge_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, amount INT NOT NULL, currency VARCHAR(10) NOT NULL, account_id VARCHAR(255) NOT NULL, order_class_id INT DEFAULT NULL, INDEX IDX_4034A3C0BF8E9A1 (order_class_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT FK_5CECC7BEA76ED395 FOREIGN KEY (user_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES abstract_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hours ADD CONSTRAINT FK_8A1ABD8D9C24126 FOREIGN KEY (day_id) REFERENCES schedule_day (id)');
        $this->addSql('ALTER TABLE opening_schedule ADD CONSTRAINT FK_B7B4F875A76ED395 FOREIGN KEY (user_id) REFERENCES professional (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09558FBEB9 FOREIGN KEY (purchase_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F096796D554 FOREIGN KEY (merchant_id) REFERENCES professional (id)');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE6867954584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES professional (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7C12FBC0 FOREIGN KEY (shelf_id) REFERENCES shelf (id)');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AA8486F9AC FOREIGN KEY (adress_id) REFERENCES adress (id)');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AAF98F144A FOREIGN KEY (logo_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AABF396750 FOREIGN KEY (id) REFERENCES abstract_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES abstract_user (id)');
        $this->addSql('ALTER TABLE schedule_day ADD CONSTRAINT FK_78696C9A2412731C FOREIGN KEY (opening_schedule_id) REFERENCES opening_schedule (id)');
        $this->addSql('ALTER TABLE slide_item ADD CONSTRAINT FK_28DBCBCA3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE slide_item ADD CONSTRAINT FK_28DBCBCA2CCC9638 FOREIGN KEY (slider_id) REFERENCES slider (id)');
        $this->addSql('ALTER TABLE slider ADD CONSTRAINT FK_CFC710074584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stripe_customer ADD CONSTRAINT FK_DC7E523AA76ED395 FOREIGN KEY (user_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE stripe_merchant ADD CONSTRAINT FK_29ECF9D2A76ED395 FOREIGN KEY (user_id) REFERENCES professional (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0BF8E9A1 FOREIGN KEY (order_class_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY FK_5CECC7BEA76ED395');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455BF396750');
        $this->addSql('ALTER TABLE hours DROP FOREIGN KEY FK_8A1ABD8D9C24126');
        $this->addSql('ALTER TABLE opening_schedule DROP FOREIGN KEY FK_B7B4F875A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09F44CABFF');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09558FBEB9');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F096796D554');
        $this->addSql('ALTER TABLE package DROP FOREIGN KEY FK_DE6867954584665A');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3DA5256D');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7C12FBC0');
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AA8486F9AC');
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AAF98F144A');
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AABF396750');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE schedule_day DROP FOREIGN KEY FK_78696C9A2412731C');
        $this->addSql('ALTER TABLE slide_item DROP FOREIGN KEY FK_28DBCBCA3DA5256D');
        $this->addSql('ALTER TABLE slide_item DROP FOREIGN KEY FK_28DBCBCA2CCC9638');
        $this->addSql('ALTER TABLE slider DROP FOREIGN KEY FK_CFC710074584665A');
        $this->addSql('ALTER TABLE stripe_customer DROP FOREIGN KEY FK_DC7E523AA76ED395');
        $this->addSql('ALTER TABLE stripe_merchant DROP FOREIGN KEY FK_29ECF9D2A76ED395');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0BF8E9A1');
        $this->addSql('DROP TABLE abstract_user');
        $this->addSql('DROP TABLE adress');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE hours');
        $this->addSql('DROP TABLE opening_schedule');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE package');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE professional');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE schedule_day');
        $this->addSql('DROP TABLE shelf');
        $this->addSql('DROP TABLE slide_item');
        $this->addSql('DROP TABLE slider');
        $this->addSql('DROP TABLE stripe');
        $this->addSql('DROP TABLE stripe_customer');
        $this->addSql('DROP TABLE stripe_merchant');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
