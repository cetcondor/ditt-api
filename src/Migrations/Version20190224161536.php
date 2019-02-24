<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190224161536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE overtime_work_log DROP CONSTRAINT FK_D467F90CDFB937B8');
        $this->addSql('ALTER TABLE overtime_work_log ADD CONSTRAINT FK_D467F90CDFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE home_office_work_log DROP CONSTRAINT FK_FAA5E2D6DFB937B8');
        $this->addSql('ALTER TABLE home_office_work_log ADD CONSTRAINT FK_FAA5E2D6DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_off_work_log DROP CONSTRAINT FK_A11B1A15DFB937B8');
        $this->addSql('ALTER TABLE time_off_work_log ADD CONSTRAINT FK_A11B1A15DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation_work_log DROP CONSTRAINT FK_21FBAD34DFB937B8');
        $this->addSql('ALTER TABLE vacation_work_log ADD CONSTRAINT FK_21FBAD34DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sick_day_work_log DROP CONSTRAINT FK_296D4043DFB937B8');
        $this->addSql('ALTER TABLE sick_day_work_log ADD CONSTRAINT FK_296D4043DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_trip_work_log DROP CONSTRAINT FK_F2BC652DFB937B8');
        $this->addSql('ALTER TABLE business_trip_work_log ADD CONSTRAINT FK_F2BC652DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_log DROP CONSTRAINT FK_F5513F59DFB937B8');
        $this->addSql('ALTER TABLE work_log ADD CONSTRAINT FK_F5513F59DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE work_log DROP CONSTRAINT fk_f5513f59dfb937b8');
        $this->addSql('ALTER TABLE work_log ADD CONSTRAINT fk_f5513f59dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE home_office_work_log DROP CONSTRAINT fk_faa5e2d6dfb937b8');
        $this->addSql('ALTER TABLE home_office_work_log ADD CONSTRAINT fk_faa5e2d6dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sick_day_work_log DROP CONSTRAINT fk_296d4043dfb937b8');
        $this->addSql('ALTER TABLE sick_day_work_log ADD CONSTRAINT fk_296d4043dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation_work_log DROP CONSTRAINT fk_21fbad34dfb937b8');
        $this->addSql('ALTER TABLE vacation_work_log ADD CONSTRAINT fk_21fbad34dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_off_work_log DROP CONSTRAINT fk_a11b1a15dfb937b8');
        $this->addSql('ALTER TABLE time_off_work_log ADD CONSTRAINT fk_a11b1a15dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_trip_work_log DROP CONSTRAINT fk_f2bc652dfb937b8');
        $this->addSql('ALTER TABLE business_trip_work_log ADD CONSTRAINT fk_f2bc652dfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE overtime_work_log DROP CONSTRAINT fk_d467f90cdfb937b8');
        $this->addSql('ALTER TABLE overtime_work_log ADD CONSTRAINT fk_d467f90cdfb937b8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
