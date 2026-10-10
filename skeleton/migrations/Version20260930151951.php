<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260930151951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ADD stripe_fees_amount DOUBLE PRECISION NOT NULL, ADD platform_fees_amount DOUBLE PRECISION NOT NULL, CHANGE amount amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE stripe CHANGE authentication_key webhook_secret LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP stripe_fees_amount, DROP platform_fees_amount, CHANGE amount amount INT NOT NULL');
        $this->addSql('ALTER TABLE stripe CHANGE webhook_secret authentication_key LONGTEXT NOT NULL');
    }
}
