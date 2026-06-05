<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602193800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY `FK_D11814ABAB9A1716`');
        $this->addSql('ALTER TABLE intervention CHANGE intervenant_id intervenant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABAB9A1716 FOREIGN KEY (intervenant_id) REFERENCES intervenant (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABAB9A1716');
        $this->addSql('ALTER TABLE intervention CHANGE intervenant_id intervenant_id INT NOT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT `FK_D11814ABAB9A1716` FOREIGN KEY (intervenant_id) REFERENCES intervenant (id)');
    }
}
