<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503223218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE verification_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, type VARCHAR(20) NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_E821C39FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE verification_code ADD CONSTRAINT FK_E821C39FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE verification_code DROP FOREIGN KEY FK_E821C39FA76ED395');
        $this->addSql('DROP TABLE verification_code');
    }
}
