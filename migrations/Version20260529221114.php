<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260529221114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiaire ADD soignant_referent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE beneficiaire ADD CONSTRAINT FK_B140D802C493594D FOREIGN KEY (soignant_referent_id) REFERENCES intervenant (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B140D802C493594D ON beneficiaire (soignant_referent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiaire DROP FOREIGN KEY FK_B140D802C493594D');
        $this->addSql('DROP INDEX IDX_B140D802C493594D ON beneficiaire');
        $this->addSql('ALTER TABLE beneficiaire DROP soignant_referent_id');
    }
}
