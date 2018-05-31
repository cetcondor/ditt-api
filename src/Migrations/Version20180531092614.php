<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180531092614 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_hours (month INT NOT NULL, year INT NOT NULL, user_id INT NOT NULL, required_hours INT NOT NULL, PRIMARY KEY(month, year, user_id))');
        $this->addSql('CREATE INDEX IDX_A2E1C6A2A76ED395 ON work_hours (user_id)');
        $this->addSql('ALTER TABLE work_hours ADD CONSTRAINT FK_A2E1C6A2A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('
DO
$do$
BEGIN
    << YEAR_LOOP >>
    FOR y IN 2018..2021 LOOP
        << MONTH_LOOP >>
        FOR m IN 1..12 LOOP
            INSERT INTO work_hours (year, month, required_hours, user_id) (SELECT y, m, 0, id FROM app_user);
        END LOOP MONTH_LOOP;
    END LOOP YEAR_LOOP;
END;
$do$;
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE work_hours');
    }
}
