<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223170718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_reservee (id INT AUTO_INCREMENT NOT NULL, formation_id INT NOT NULL, utilisateur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_reservation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, INDEX IDX_FD378CA55200282E (formation_id), INDEX IDX_FD378CA5FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_reservee ADD CONSTRAINT FK_FD378CA55200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_reservee ADD CONSTRAINT FK_FD378CA5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE partenariat CHANGE statut statut VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_reservee DROP FOREIGN KEY FK_FD378CA55200282E');
        $this->addSql('ALTER TABLE formation_reservee DROP FOREIGN KEY FK_FD378CA5FB88E14F');
        $this->addSql('DROP TABLE formation_reservee');
        $this->addSql('ALTER TABLE partenariat CHANGE statut statut VARCHAR(255) NOT NULL');
    }
}
