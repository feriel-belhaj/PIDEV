<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220140250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B873A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E33BD3B873A201E5 ON candidature (createur_id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_B26681E73A201E5 ON evenement (createur_id)');
        $this->addSql('ALTER TABLE produit ADD createur_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2773A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC2773A201E5 ON produit (createur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B873A201E5');
        $this->addSql('DROP INDEX IDX_E33BD3B873A201E5 ON candidature');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E73A201E5');
        $this->addSql('DROP INDEX IDX_B26681E73A201E5 ON evenement');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2773A201E5');
        $this->addSql('DROP INDEX IDX_29A5EC2773A201E5 ON produit');
        $this->addSql('ALTER TABLE produit DROP createur_id');
    }
}
