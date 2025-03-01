<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301215840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description column to Creation table';
    }

    public function up(Schema $schema): void
    {
        // Add the description column as TEXT and nullable
        $this->addSql('ALTER TABLE creation ADD description TEXT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the description column
        $this->addSql('ALTER TABLE creation DROP COLUMN description');
    }
}
