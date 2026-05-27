<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526144557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, plan_tarifaire_id INT NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_351268BB177F4E0D (plan_tarifaire_id), INDEX IDX_351268BBA4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, siret VARCHAR(50) NOT NULL, telephone VARCHAR(50) NOT NULL, adresse VARCHAR(50) NOT NULL, date_inscription DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE plan_tarifaire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prix NUMERIC(10, 2) NOT NULL, fonctionnalites VARCHAR(50) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB177F4E0D FOREIGN KEY (plan_tarifaire_id) REFERENCES plan_tarifaire (id)');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE utilisateur ADD entreprise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3A4AEAFEA ON utilisateur (entreprise_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB177F4E0D');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBA4AEAFEA');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE plan_tarifaire');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3A4AEAFEA');
        $this->addSql('DROP INDEX IDX_1D1C63B3A4AEAFEA ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP entreprise_id');
    }
}
