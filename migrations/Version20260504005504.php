<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504005504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('ALTER TABLE verification_code DROP FOREIGN KEY `FK_E821C39FA76ED395`');
        $this->addSql('DROP INDEX IDX_E821C39FA76ED395 ON verification_code');
        $this->addSql('ALTER TABLE verification_code ADD email VARCHAR(255) NOT NULL, DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE verification_code ADD user_id INT NOT NULL, DROP email');
        $this->addSql('ALTER TABLE verification_code ADD CONSTRAINT `FK_E821C39FA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E821C39FA76ED395 ON verification_code (user_id)');
    }
}
