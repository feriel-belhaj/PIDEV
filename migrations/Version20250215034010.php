<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215034010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement CHANGE startdate startdate DATETIME DEFAULT NULL, CHANGE enddate enddate DATETIME DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE goalamount goalamount DOUBLE PRECISION DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE imageurl imageurl VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement CHANGE startdate startdate DATETIME NOT NULL, CHANGE enddate enddate DATETIME NOT NULL, CHANGE localisation localisation VARCHAR(255) NOT NULL, CHANGE goalamount goalamount DOUBLE PRECISION NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE imageurl imageurl VARCHAR(255) NOT NULL');
    }
}
