<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319162921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, amount INT NOT NULL, customer_id INT DEFAULT NULL, INDEX IDX_6D28840D9395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9395C3F3 FOREIGN KEY (customer_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY `FK_4034A3C0BF8E9A1`');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY `FK_F5299398A76ED395`');
        $this->addSql('DROP INDEX IDX_F5299398A76ED395 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD merchant_id INT DEFAULT NULL, CHANGE user_id payment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993986796D554 FOREIGN KEY (merchant_id) REFERENCES professional (id)');
        $this->addSql('CREATE INDEX IDX_F52993984C3A3BB ON `order` (payment_id)');
        $this->addSql('CREATE INDEX IDX_F52993986796D554 ON `order` (merchant_id)');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY `FK_52EA1F096796D554`');
        $this->addSql('DROP INDEX IDX_52EA1F096796D554 ON order_item');
        $this->addSql('ALTER TABLE order_item DROP merchant_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, transfer_id VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_general_ci`, charge_id VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_general_ci`, created_at DATETIME NOT NULL, amount INT NOT NULL, currency VARCHAR(10) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_general_ci`, account_id VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_general_ci`, order_class_id INT DEFAULT NULL, INDEX IDX_4034A3C0BF8E9A1 (order_class_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT `FK_4034A3C0BF8E9A1` FOREIGN KEY (order_class_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9395C3F3');
        $this->addSql('DROP TABLE payment');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984C3A3BB');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993986796D554');
        $this->addSql('DROP INDEX IDX_F52993984C3A3BB ON `order`');
        $this->addSql('DROP INDEX IDX_F52993986796D554 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD user_id INT DEFAULT NULL, DROP payment_id, DROP merchant_id');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT `FK_F5299398A76ED395` FOREIGN KEY (user_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON `order` (user_id)');
        $this->addSql('ALTER TABLE order_item ADD merchant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT `FK_52EA1F096796D554` FOREIGN KEY (merchant_id) REFERENCES professional (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_52EA1F096796D554 ON order_item (merchant_id)');
    }
}
