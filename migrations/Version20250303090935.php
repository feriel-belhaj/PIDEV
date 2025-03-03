<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303090935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B873A201E5');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B873A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D73A201E5');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D973A201E5');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D973A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF73A201E5');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partenariat DROP FOREIGN KEY FK_BF53DC8673A201E5');
        $this->addSql('ALTER TABLE partenariat ADD CONSTRAINT FK_BF53DC8673A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2773A201E5');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2773A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B873A201E5');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B873A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D73A201E5');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D973A201E5');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D973A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF73A201E5');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE partenariat DROP FOREIGN KEY FK_BF53DC8673A201E5');
        $this->addSql('ALTER TABLE partenariat ADD CONSTRAINT FK_BF53DC8673A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2773A201E5');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2773A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
    }
}
