<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250217_Creation extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Creation table for proper file handling';
    }

    public function up(Schema $schema): void
    {
        // Update the image column to be nullable and increase its length
        $this->addSql('ALTER TABLE creation MODIFY image VARCHAR(255) NULL');
        
        // Add an index on the image column for better performance
        $this->addSql('CREATE INDEX IDX_57EE857993CB796C ON creation (image)');
    }

    public function down(Schema $schema): void
    {
        // Remove the index
        $this->addSql('DROP INDEX IDX_57EE857993CB796C ON creation');
        
        // Revert the image column changes
        $this->addSql('ALTER TABLE creation MODIFY image VARCHAR(255) NOT NULL');
    }
}
