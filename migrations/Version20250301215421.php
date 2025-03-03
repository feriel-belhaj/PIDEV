<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301215421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature ADD score_nlp DOUBLE PRECISION DEFAULT NULL, ADD score_artistique DOUBLE PRECISION DEFAULT NULL, ADD analysis_result LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE partenariat CHANGE statut statut VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP score_nlp, DROP score_artistique, DROP analysis_result');
        $this->addSql('ALTER TABLE partenariat CHANGE statut statut VARCHAR(255) DEFAULT NULL');
    }
}
