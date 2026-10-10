<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261003113543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD stripe_fees_amount INT NOT NULL, ADD platform_fees_amount INT NOT NULL');
        $this->addSql('ALTER TABLE payment DROP amount, DROP stripe_fees_amount, DROP platform_fees_amount');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP stripe_fees_amount, DROP platform_fees_amount');
        $this->addSql('ALTER TABLE payment ADD amount INT NOT NULL, ADD stripe_fees_amount INT NOT NULL, ADD platform_fees_amount INT NOT NULL');
    }
}
