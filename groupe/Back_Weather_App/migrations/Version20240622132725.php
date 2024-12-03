<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240622132725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_C11AFC4121BDB235 FOREIGN KEY (station_id) REFERENCES station (id)');
        $this->addSql('ALTER TABLE station ADD CONSTRAINT FK_9F39F8B14D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D4A76ED395');
        $this->addSql('ALTER TABLE reading DROP FOREIGN KEY FK_C11AFC4121BDB235');
        $this->addSql('ALTER TABLE station DROP FOREIGN KEY FK_9F39F8B14D2A7E12');
    }
}
