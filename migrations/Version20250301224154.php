<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301224154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add utilisateur_id column to creation table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE creation ADD utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE creation ADD CONSTRAINT FK_57EE85CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_57EE85CFB88E14F ON creation (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE creation DROP FOREIGN KEY FK_57EE85CFB88E14F');
        $this->addSql('DROP INDEX IDX_57EE85CFB88E14F ON creation');
        $this->addSql('ALTER TABLE creation DROP utilisateur_id');
    }
}
