<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605212537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, plan_tarifaire_id INT NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_351268BB177F4E0D (plan_tarifaire_id), INDEX IDX_351268BBA4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE administrateur (id INT AUTO_INCREMENT NOT NULL, niveau_acces VARCHAR(255) NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_32EB52E8FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE aidant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, lien VARCHAR(100) DEFAULT NULL, beneficiaire_id INT NOT NULL, INDEX IDX_B70F5A2F5AF81F68 (beneficiaire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE beneficiaire (id INT AUTO_INCREMENT NOT NULL, date_naissance DATE NOT NULL, adresse VARCHAR(255) NOT NULL, latitude NUMERIC(10, 7) NOT NULL, longitude NUMERIC(10, 7) NOT NULL, pathologie VARCHAR(255) DEFAULT NULL, niveau_risque VARCHAR(50) NOT NULL, rdv_par_semaine INT DEFAULT NULL, utilisateur_id INT NOT NULL, admin_createur_id INT NOT NULL, soignant_referent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B140D802FB88E14F (utilisateur_id), INDEX IDX_B140D802EEB848EC (admin_createur_id), INDEX IDX_B140D802C493594D (soignant_referent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE compte_rendu (id INT AUTO_INCREMENT NOT NULL, date_redaction DATETIME NOT NULL, contenu LONGTEXT NOT NULL, mode_redaction VARCHAR(255) NOT NULL, valide TINYINT NOT NULL, intervention_id INT NOT NULL, UNIQUE INDEX UNIQ_D39E69D28EAE3863 (intervention_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, siret VARCHAR(50) NOT NULL, telephone VARCHAR(50) NOT NULL, adresse VARCHAR(50) NOT NULL, date_inscription DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, date_signalement DATETIME NOT NULL, description LONGTEXT NOT NULL, gravite VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, zone VARCHAR(255) DEFAULT NULL, intervention_id INT NOT NULL, INDEX IDX_3D03A11A8EAE3863 (intervention_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE intervenant (id INT AUTO_INCREMENT NOT NULL, specialite VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, disponibilite TINYINT NOT NULL, statut VARCHAR(30) DEFAULT \'actif\', rayon_intervention DOUBLE PRECISION NOT NULL, vehicule VARCHAR(100) DEFAULT NULL, created_at DATETIME DEFAULT NULL, utilisateur_id INT NOT NULL, admin_createur_id INT NOT NULL, UNIQUE INDEX UNIQ_73D0145CFB88E14F (utilisateur_id), INDEX IDX_73D0145CEEB848EC (admin_createur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, type_intervention VARCHAR(255) NOT NULL, observations LONGTEXT DEFAULT NULL, intervenant_id INT DEFAULT NULL, beneficiaire_id INT NOT NULL, planning_id INT NOT NULL, INDEX IDX_D11814ABAB9A1716 (intervenant_id), INDEX IDX_D11814AB5AF81F68 (beneficiaire_id), INDEX IDX_D11814AB3D865311 (planning_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, date_envoi DATETIME NOT NULL, lu TINYINT NOT NULL, type VARCHAR(255) NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT NOT NULL, beneficiaire_id INT DEFAULT NULL, INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX IDX_B6BD307FA4F84F6E (destinataire_id), INDEX IDX_B6BD307F5AF81F68 (beneficiaire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, lu TINYINT NOT NULL, created_at DATETIME DEFAULT NULL, message VARCHAR(500) DEFAULT NULL, utilisateur_id INT NOT NULL, compte_rendu_id INT DEFAULT NULL, INDEX IDX_BF5476CAFB88E14F (utilisateur_id), INDEX IDX_BF5476CA4BC44A10 (compte_rendu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE plan_tarifaire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prix NUMERIC(10, 2) NOT NULL, fonctionnalites VARCHAR(50) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, semaine DATE NOT NULL, statut VARCHAR(255) NOT NULL, admin_id INT NOT NULL, INDEX IDX_D499BFF6642B8210 (admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE planning_intervenant (planning_id INT NOT NULL, intervenant_id INT NOT NULL, INDEX IDX_3EFBD4053D865311 (planning_id), INDEX IDX_3EFBD405AB9A1716 (intervenant_id), PRIMARY KEY (planning_id, intervenant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mdp VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, entreprise_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), INDEX IDX_1D1C63B3A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB177F4E0D FOREIGN KEY (plan_tarifaire_id) REFERENCES plan_tarifaire (id)');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE administrateur ADD CONSTRAINT FK_32EB52E8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE aidant ADD CONSTRAINT FK_B70F5A2F5AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE beneficiaire ADD CONSTRAINT FK_B140D802FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE beneficiaire ADD CONSTRAINT FK_B140D802EEB848EC FOREIGN KEY (admin_createur_id) REFERENCES administrateur (id)');
        $this->addSql('ALTER TABLE beneficiaire ADD CONSTRAINT FK_B140D802C493594D FOREIGN KEY (soignant_referent_id) REFERENCES intervenant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE compte_rendu ADD CONSTRAINT FK_D39E69D28EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervenant ADD CONSTRAINT FK_73D0145CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE intervenant ADD CONSTRAINT FK_73D0145CEEB848EC FOREIGN KEY (admin_createur_id) REFERENCES administrateur (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABAB9A1716 FOREIGN KEY (intervenant_id) REFERENCES intervenant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB5AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiaire (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB3D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F5AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiaire (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4BC44A10 FOREIGN KEY (compte_rendu_id) REFERENCES compte_rendu (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6642B8210 FOREIGN KEY (admin_id) REFERENCES administrateur (id)');
        $this->addSql('ALTER TABLE planning_intervenant ADD CONSTRAINT FK_3EFBD4053D865311 FOREIGN KEY (planning_id) REFERENCES planning (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planning_intervenant ADD CONSTRAINT FK_3EFBD405AB9A1716 FOREIGN KEY (intervenant_id) REFERENCES intervenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB177F4E0D');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBA4AEAFEA');
        $this->addSql('ALTER TABLE administrateur DROP FOREIGN KEY FK_32EB52E8FB88E14F');
        $this->addSql('ALTER TABLE aidant DROP FOREIGN KEY FK_B70F5A2F5AF81F68');
        $this->addSql('ALTER TABLE beneficiaire DROP FOREIGN KEY FK_B140D802FB88E14F');
        $this->addSql('ALTER TABLE beneficiaire DROP FOREIGN KEY FK_B140D802EEB848EC');
        $this->addSql('ALTER TABLE beneficiaire DROP FOREIGN KEY FK_B140D802C493594D');
        $this->addSql('ALTER TABLE compte_rendu DROP FOREIGN KEY FK_D39E69D28EAE3863');
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11A8EAE3863');
        $this->addSql('ALTER TABLE intervenant DROP FOREIGN KEY FK_73D0145CFB88E14F');
        $this->addSql('ALTER TABLE intervenant DROP FOREIGN KEY FK_73D0145CEEB848EC');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABAB9A1716');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB5AF81F68');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB3D865311');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4F84F6E');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F5AF81F68');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4BC44A10');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6642B8210');
        $this->addSql('ALTER TABLE planning_intervenant DROP FOREIGN KEY FK_3EFBD4053D865311');
        $this->addSql('ALTER TABLE planning_intervenant DROP FOREIGN KEY FK_3EFBD405AB9A1716');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3A4AEAFEA');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE administrateur');
        $this->addSql('DROP TABLE aidant');
        $this->addSql('DROP TABLE beneficiaire');
        $this->addSql('DROP TABLE compte_rendu');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE intervenant');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE plan_tarifaire');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE planning_intervenant');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
