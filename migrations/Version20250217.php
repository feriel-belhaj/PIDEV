<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename decription to description in creation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE creation CHANGE decription description VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE creation CHANGE description decription VARCHAR(255) NOT NULL');
    }
}
