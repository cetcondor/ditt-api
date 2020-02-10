<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180620135949 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE business_trip_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, time_approved TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_rejected TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F2BC652DFB937B8 ON business_trip_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN business_trip_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN business_trip_work_log.time_approved IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN business_trip_work_log.time_rejected IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE time_off_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, time_approved TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_rejected TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A11B1A15DFB937B8 ON time_off_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN time_off_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN time_off_work_log.time_approved IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN time_off_work_log.time_rejected IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE home_office_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, time_approved TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_rejected TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FAA5E2D6DFB937B8 ON home_office_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN home_office_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN home_office_work_log.time_approved IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN home_office_work_log.time_rejected IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE business_trip_work_log ADD CONSTRAINT FK_F2BC652DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_off_work_log ADD CONSTRAINT FK_A11B1A15DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE home_office_work_log ADD CONSTRAINT FK_FAA5E2D6DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE business_trip_work_log');
        $this->addSql('DROP TABLE time_off_work_log');
        $this->addSql('DROP TABLE home_office_work_log');
    }
}
