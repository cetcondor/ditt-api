<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180813091841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE business_trip_work_log ADD purpose TEXT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD destination TEXT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD transport TEXT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD expected_departure TEXT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD expected_arrival TEXT NULL');
        $this->addSql('UPDATE business_trip_work_log SET purpose = \'-\', destination = \'-\', transport = \'-\', expected_departure = \'-\', expected_arrival = \'-\'');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN purpose SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN destination SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN transport SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN expected_departure SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN expected_arrival SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE business_trip_work_log DROP purpose');
        $this->addSql('ALTER TABLE business_trip_work_log DROP destination');
        $this->addSql('ALTER TABLE business_trip_work_log DROP transport');
        $this->addSql('ALTER TABLE business_trip_work_log DROP expected_departure');
        $this->addSql('ALTER TABLE business_trip_work_log DROP expected_arrival');
    }
}
