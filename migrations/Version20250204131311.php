<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204131311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE don (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, id_don INT NOT NULL, donationdate DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount DOUBLE PRECISION NOT NULL, paymentref VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, INDEX IDX_F8F081D9FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, id_event INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, startdate DATETIME NOT NULL, enddate DATETIME NOT NULL, localisation VARCHAR(255) NOT NULL, goalamount DOUBLE PRECISION NOT NULL, collectedamount DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, imageurl VARCHAR(255) NOT NULL, createdat DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D9FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D9FD02F13');
        $this->addSql('DROP TABLE don');
        $this->addSql('DROP TABLE evenement');
    }
}
