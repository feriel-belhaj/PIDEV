<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220133635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       
        $this->addSql('ALTER TABLE formation ADD createur_id INT NOT NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_404021BF73A201E5 ON formation (createur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF73A201E5');
        $this->addSql('DROP INDEX IDX_404021BF73A201E5 ON formation');
        $this->addSql('ALTER TABLE formation DROP createur_id');
    }
}
