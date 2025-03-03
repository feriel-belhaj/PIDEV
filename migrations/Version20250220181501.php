<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220181501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificat DROP FOREIGN KEY FK_27448F7773A201E5');
        $this->addSql('DROP INDEX IDX_27448F7773A201E5 ON certificat');
        $this->addSql('ALTER TABLE certificat DROP createur_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificat ADD createur_id INT NOT NULL');
        $this->addSql('ALTER TABLE certificat ADD CONSTRAINT FK_27448F7773A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_27448F7773A201E5 ON certificat (createur_id)');
    }
}
