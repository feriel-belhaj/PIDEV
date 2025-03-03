<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217112248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commandeProduits (commande_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_C5A82E0782EA2E54 (commande_id), INDEX IDX_C5A82E07F347EFB (produit_id), PRIMARY KEY(commande_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commandeProduits ADD CONSTRAINT FK_C5A82E0782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commandeProduits ADD CONSTRAINT FK_C5A82E07F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_produit ADD id INT AUTO_INCREMENT NOT NULL, ADD quantite INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commandeProduits DROP FOREIGN KEY FK_C5A82E0782EA2E54');
        $this->addSql('ALTER TABLE commandeProduits DROP FOREIGN KEY FK_C5A82E07F347EFB');
        $this->addSql('DROP TABLE commandeProduits');
        $this->addSql('ALTER TABLE commande_produit MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON commande_produit');
        $this->addSql('ALTER TABLE commande_produit DROP id, DROP quantite');
        $this->addSql('ALTER TABLE commande_produit ADD PRIMARY KEY (commande_id, produit_id)');
    }
}
