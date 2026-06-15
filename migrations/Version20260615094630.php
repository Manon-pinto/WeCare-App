<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260615094630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_devis (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, telephone VARCHAR(20) NOT NULL, structure VARCHAR(150) NOT NULL, type_structure VARCHAR(50) NOT NULL, nb_intervenants VARCHAR(30) DEFAULT NULL, nb_beneficiaires VARCHAR(30) DEFAULT NULL, plan VARCHAR(20) NOT NULL, message LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, montant DOUBLE PRECISION DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE demande_devis');
    }
}
