<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202135259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business_trip_work_log ADD planned_end_hour INT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD planned_end_minute INT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD planned_start_hour INT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ADD planned_start_minute INT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ADD planned_end_hour INT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ADD planned_end_minute INT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ADD planned_start_hour INT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ADD planned_start_minute INT NULL');

        $this->addSql('UPDATE business_trip_work_log SET planned_end_hour = 0, planned_end_minute = 0, planned_start_hour = 0, planned_start_minute = 0');
        $this->addSql('UPDATE home_office_work_log SET planned_end_hour = 0, planned_end_minute = 0, planned_start_hour = 0, planned_start_minute = 0');

        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN planned_end_hour SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN planned_end_minute SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN planned_start_hour SET NOT NULL');
        $this->addSql('ALTER TABLE business_trip_work_log ALTER COLUMN planned_start_minute SET NOT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ALTER COLUMN planned_end_hour SET NOT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ALTER COLUMN planned_end_minute SET NOT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ALTER COLUMN planned_start_hour SET NOT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ALTER COLUMN planned_start_minute SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business_trip_work_log DROP planned_end_hour');
        $this->addSql('ALTER TABLE business_trip_work_log DROP planned_end_minute');
        $this->addSql('ALTER TABLE business_trip_work_log DROP planned_start_hour');
        $this->addSql('ALTER TABLE business_trip_work_log DROP planned_start_minute');
        $this->addSql('ALTER TABLE home_office_work_log DROP planned_end_hour');
        $this->addSql('ALTER TABLE home_office_work_log DROP planned_end_minute');
        $this->addSql('ALTER TABLE home_office_work_log DROP planned_start_hour');
        $this->addSql('ALTER TABLE home_office_work_log DROP planned_start_minute');
    }
}
