<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213135659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B85C1628E0');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B85C1628E0 FOREIGN KEY (partenariat_id) REFERENCES partenariat (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B85C1628E0');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B85C1628E0 FOREIGN KEY (partenariat_id) REFERENCES partenariat (id)');
    }
}
