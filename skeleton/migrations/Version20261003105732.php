<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261003105732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment CHANGE amount amount INT NOT NULL, CHANGE stripe_fees_amount stripe_fees_amount INT NOT NULL, CHANGE platform_fees_amount platform_fees_amount INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment CHANGE amount amount DOUBLE PRECISION NOT NULL, CHANGE stripe_fees_amount stripe_fees_amount DOUBLE PRECISION NOT NULL, CHANGE platform_fees_amount platform_fees_amount DOUBLE PRECISION NOT NULL');
    }
}
