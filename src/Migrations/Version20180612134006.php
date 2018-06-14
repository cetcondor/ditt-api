<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180612134006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_month (id SERIAL NOT NULL, user_id INT NOT NULL, month INT NOT NULL, year INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A64D6B29A76ED395 ON work_month (user_id)');
        $this->addSql('CREATE UNIQUE INDEX work_month_idx ON work_month (month, user_id, year)');
        $this->addSql('ALTER TABLE work_month ADD CONSTRAINT FK_A64D6B29A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('
DO
$do$
BEGIN
    << YEAR_LOOP >>
    FOR y IN 2018..2021 LOOP
        << MONTH_LOOP >>
        FOR m IN 1..12 LOOP
            INSERT INTO work_month (year, month, user_id) (SELECT y, m, id FROM app_user);
        END LOOP MONTH_LOOP;
    END LOOP YEAR_LOOP;
END;
$do$;
        ');
        $this->addSql('ALTER TABLE work_log DROP CONSTRAINT fk_f5513f59a76ed395');
        $this->addSql('DROP INDEX idx_f5513f59a76ed395');
        $this->addSql('ALTER TABLE work_log ADD work_month_id INT');
        $this->addSql('
            UPDATE work_log
            SET work_month_id = subquery.id
            FROM (SELECT * FROM work_month) AS subquery
            WHERE date_part(\'month\', work_log.start_time) = subquery.month
            AND date_part(\'year\', work_log.start_time) = subquery.year
            AND work_log.user_id = subquery.user_id

        ');
        $this->addSql('ALTER TABLE work_log ALTER COLUMN work_month_id SET NOT NULL');
        $this->addSql('ALTER TABLE work_log DROP user_id');
        $this->addSql('ALTER TABLE work_log ADD CONSTRAINT FK_F5513F59DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F5513F59DFB937B8 ON work_log (work_month_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE work_log DROP CONSTRAINT FK_F5513F59DFB937B8');
        $this->addSql('DROP TABLE work_month');
        $this->addSql('DROP INDEX IDX_F5513F59DFB937B8');
        $this->addSql('ALTER TABLE work_log RENAME COLUMN work_month_id TO user_id');
        $this->addSql('ALTER TABLE work_log ADD CONSTRAINT fk_f5513f59a76ed395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f5513f59a76ed395 ON work_log (user_id)');
    }
}
