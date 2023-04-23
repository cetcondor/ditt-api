<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421191156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE work_hours');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE work_hours (month INT NOT NULL, year INT NOT NULL, user_id INT NOT NULL, required_hours INT NOT NULL, PRIMARY KEY(month, user_id, year))');
        $this->addSql('CREATE INDEX idx_a2e1c6a2bb827337 ON work_hours (year)');
        $this->addSql('CREATE INDEX idx_a2e1c6a2a76ed395 ON work_hours (user_id)');
        $this->addSql('ALTER TABLE work_hours ADD CONSTRAINT fk_a2e1c6a2a76ed395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_hours ADD CONSTRAINT fk_a2e1c6a2bb827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
