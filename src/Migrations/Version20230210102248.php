<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230210102248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id SERIAL NOT NULL, user_id INT NOT NULL, start_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_day_based BOOLEAN NOT NULL, is_monday_included BOOLEAN NOT NULL, is_tuesday_included BOOLEAN NOT NULL, is_wednesday_included BOOLEAN NOT NULL, is_thursday_included BOOLEAN NOT NULL, is_friday_included BOOLEAN NOT NULL, weekly_working_days INT NOT NULL, weekly_working_hours DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E98F2859A76ED395 ON contract (user_id)');
        $this->addSql('COMMENT ON COLUMN contract.start_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract.end_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('
CREATE OR REPLACE FUNCTION end_of_month(date)
    RETURNS date AS
$$
SELECT (date_trunc(\'month\', $1) + interval \'1 month\' - interval \'1 day\')::date;
$$ LANGUAGE \'sql\'
    IMMUTABLE STRICT;
    ');
        $this->addSql('
DO
$do$
    DECLARE
        appUser record;
        workHours record;
        lastSupportedYear record;
        lastYear integer;
        lastMonth integer;
        lastRequiredHours integer;
        areVariablesInitialized bool;
    BEGIN
        TRUNCATE TABLE contract;
        SELECT * INTO lastSupportedYear FROM supported_year ORDER BY year DESC LIMIT 1;
        FOR appUser IN (SELECT * FROM app_user)
            LOOP
                areVariablesInitialized := false;
                FOR workHours IN (SELECT * FROM work_hours WHERE user_id = appUser.id ORDER BY year, month)
                    LOOP
                        IF NOT areVariablesInitialized THEN
                            lastYear := workHours.year;
                            lastMonth := workHours.month;
                            lastRequiredHours := workHours.required_hours;
                            areVariablesInitialized := true;
                        ELSE
                            IF lastRequiredHours != workHours.required_hours THEN
                                IF lastRequiredHours > 0 THEN
                                    INSERT INTO contract (user_id, start_date_time, end_date_time, is_day_based, is_monday_included, is_tuesday_included, is_wednesday_included, is_thursday_included, is_friday_included, weekly_working_days, weekly_working_hours) VALUES (appUser.id, make_date(lastYear, lastMonth, 1), end_of_month(make_date(workHours.year, workHours.month, 1)), true, true, true, true, true, true, 5, lastRequiredHours / 3600.0 * 5);
                                END IF;
                                lastYear := workHours.year;
                                lastMonth := workHours.month;
                                lastRequiredHours := workHours.required_hours;
                            END IF;
                        END IF;
                    END LOOP;
                IF lastRequiredHours > 0 THEN
                    INSERT INTO contract (user_id, start_date_time, is_day_based, is_monday_included, is_tuesday_included, is_wednesday_included, is_thursday_included, is_friday_included, weekly_working_days, weekly_working_hours) VALUES (appUser.id, make_date(lastYear, lastMonth, 1), true, true, true, true, true, true, 5, lastRequiredHours / 3600.0 * 5);
                END IF;
            END LOOP;
    END;
$do$;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contract');
    }
}
