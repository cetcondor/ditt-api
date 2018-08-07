<?php

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180806135613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE app_user_year_stats (id SERIAL NOT NULL, user_id INT DEFAULT NULL, year INT NOT NULL, vacation_days_used INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_994AA18A76ED395 ON app_user_year_stats (user_id)');
        $this->addSql('CREATE INDEX year_user_idx ON app_user_year_stats (year, user_id)');
        $this->addSql('CREATE INDEX year_idx ON app_user_year_stats (year)');
        $this->addSql('ALTER TABLE app_user_year_stats ADD CONSTRAINT FK_994AA18A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(<<<EOD
INSERT INTO app_user_year_stats (user_id, year, vacation_days_used)
SELECT id, 2018, 0 FROM app_user UNION
SELECT id, 2019, 0 FROM app_user UNION
SELECT id, 2020, 0 FROM app_user UNION
SELECT id, 2021, 0 FROM app_user;
EOD
        );
        $this->addSql(<<<EOD
CREATE OR REPLACE FUNCTION update_user_vacation_day_stats() RETURNS TRIGGER AS
\$update_user_vacation_day_stats\$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        UPDATE
            app_user_year_stats ays
        SET
            vacation_days_used = ( 
                SELECT COUNT(*)
                FROM vacation_work_log vwl
                JOIN work_month wm ON wm.id = vwl.work_month_id
                WHERE wm.user_id = (SELECT user_id FROM work_month WHERE id = OLD.work_month_id)  
                    AND wm.year = (SELECT year FROM work_month WHERE id = OLD.work_month_id)
                    AND vwl.time_rejected IS NULL
            )
        WHERE ays.user_id = (SELECT user_id FROM work_month WHERE id = OLD.work_month_id)
            AND ays.year = (SELECT year FROM work_month WHERE id = OLD.work_month_id); 
        RETURN OLD;
    ELSIF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
        UPDATE
            app_user_year_stats ays
        SET
            vacation_days_used = ( 
                SELECT COUNT(*)
                FROM vacation_work_log vwl
                JOIN work_month wm ON wm.id = vwl.work_month_id
                WHERE wm.user_id = (SELECT user_id FROM work_month WHERE id = NEW.work_month_id)  
                    AND wm.year = (SELECT year FROM work_month WHERE id = NEW.work_month_id)
                    AND vwl.time_rejected IS NULL
            )
        WHERE ays.user_id = (SELECT user_id FROM work_month WHERE id = NEW.work_month_id)
            AND ays.year = (SELECT year FROM work_month WHERE id = NEW.work_month_id);
        RETURN NEW;
    END IF;
    RETURN NEW;
END;
\$update_user_vacation_day_stats\$
LANGUAGE plpgsql;
EOD
        );
        $this->addSql(
            <<<EOD
CREATE TRIGGER update_user_vacation_day_stats_trigger
AFTER INSERT OR UPDATE OR DELETE ON vacation_work_log
FOR EACH ROW EXECUTE PROCEDURE update_user_vacation_day_stats();
EOD
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TRIGGER update_user_vacation_day_stats_trigger ON vacation_work_log CASCADE');
        $this->addSql('DROP TABLE app_user_year_stats');
    }
}
