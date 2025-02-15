<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205150910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur_formation (utilisateur_id INT NOT NULL, formation_id INT NOT NULL, INDEX IDX_20EED493FB88E14F (utilisateur_id), INDEX IDX_20EED4935200282E (formation_id), PRIMARY KEY(utilisateur_id, formation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_evenement (utilisateur_id INT NOT NULL, evenement_id INT NOT NULL, INDEX IDX_6B889D32FB88E14F (utilisateur_id), INDEX IDX_6B889D32FD02F13 (evenement_id), PRIMARY KEY(utilisateur_id, evenement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_produit (utilisateur_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_53AE1BB5FB88E14F (utilisateur_id), INDEX IDX_53AE1BB5F347EFB (produit_id), PRIMARY KEY(utilisateur_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_partenariat (utilisateur_id INT NOT NULL, partenariat_id INT NOT NULL, INDEX IDX_4BB5D115FB88E14F (utilisateur_id), INDEX IDX_4BB5D1155C1628E0 (partenariat_id), PRIMARY KEY(utilisateur_id, partenariat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE utilisateur_formation ADD CONSTRAINT FK_20EED493FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_formation ADD CONSTRAINT FK_20EED4935200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_evenement ADD CONSTRAINT FK_6B889D32FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_evenement ADD CONSTRAINT FK_6B889D32FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_produit ADD CONSTRAINT FK_53AE1BB5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_produit ADD CONSTRAINT FK_53AE1BB5F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_partenariat ADD CONSTRAINT FK_4BB5D115FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_partenariat ADD CONSTRAINT FK_4BB5D1155C1628E0 FOREIGN KEY (partenariat_id) REFERENCES partenariat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creation ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE creation ADD CONSTRAINT FK_57EE8574FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_57EE8574FB88E14F ON creation (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur_formation DROP FOREIGN KEY FK_20EED493FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_formation DROP FOREIGN KEY FK_20EED4935200282E');
        $this->addSql('ALTER TABLE utilisateur_evenement DROP FOREIGN KEY FK_6B889D32FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_evenement DROP FOREIGN KEY FK_6B889D32FD02F13');
        $this->addSql('ALTER TABLE utilisateur_produit DROP FOREIGN KEY FK_53AE1BB5FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_produit DROP FOREIGN KEY FK_53AE1BB5F347EFB');
        $this->addSql('ALTER TABLE utilisateur_partenariat DROP FOREIGN KEY FK_4BB5D115FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_partenariat DROP FOREIGN KEY FK_4BB5D1155C1628E0');
        $this->addSql('DROP TABLE utilisateur_formation');
        $this->addSql('DROP TABLE utilisateur_evenement');
        $this->addSql('DROP TABLE utilisateur_produit');
        $this->addSql('DROP TABLE utilisateur_partenariat');
        $this->addSql('ALTER TABLE creation DROP FOREIGN KEY FK_57EE8574FB88E14F');
        $this->addSql('DROP INDEX IDX_57EE8574FB88E14F ON creation');
        $this->addSql('ALTER TABLE creation DROP utilisateur_id');
    }
}
