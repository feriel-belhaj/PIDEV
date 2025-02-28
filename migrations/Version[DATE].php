<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

return new class extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add categorie column to evenement table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement ADD categorie VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP COLUMN categorie');
    }
}; 