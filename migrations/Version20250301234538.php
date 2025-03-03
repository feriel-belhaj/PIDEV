<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301234538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE don ADD user_id INT DEFAULT NULL, CHANGE message message LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D9A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_F8F081D9A76ED395 ON don (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D9A76ED395');
        $this->addSql('DROP INDEX IDX_F8F081D9A76ED395 ON don');
        $this->addSql('ALTER TABLE don DROP user_id, CHANGE message message VARCHAR(500) DEFAULT NULL');
    }
}
