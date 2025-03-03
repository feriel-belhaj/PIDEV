<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220120542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partenariat CHANGE createur_id createur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partenariat ADD CONSTRAINT FK_BF53DC8673A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_BF53DC8673A201E5 ON partenariat (createur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partenariat DROP FOREIGN KEY FK_BF53DC8673A201E5');
        $this->addSql('DROP INDEX IDX_BF53DC8673A201E5 ON partenariat');
        $this->addSql('ALTER TABLE partenariat CHANGE createur_id createur_id INT NOT NULL');
    }
}
