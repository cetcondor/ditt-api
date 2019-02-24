<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190224154722 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Forgotten in previous migrations
        $this->addSql('DELETE FROM app_user_year_stats WHERE year = 2021');

        $this->addSql('CREATE TABLE supported_holiday (day INT NOT NULL, month INT NOT NULL, year INT NOT NULL, PRIMARY KEY(day, month, year))');
        $this->addSql('CREATE INDEX IDX_E375C161BB827337 ON supported_holiday (year)');
        $this->addSql('CREATE TABLE supported_year (year INT NOT NULL, PRIMARY KEY(year))');
        $this->addSql('ALTER TABLE supported_holiday ADD CONSTRAINT FK_E375C161BB827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Copy values from existing config
        $this->addSql('
          INSERT INTO supported_year
          (year) VALUES
          (2018),
          (2019),
          (2020)'
        );
        $this->addSql('
          INSERT INTO supported_holiday
          (year, month, day) VALUES 
            (2018, 01, 01),
            (2018, 03, 30),
            (2018, 04, 02),
            (2018, 05, 01),
            (2018, 05, 10),
            (2018, 05, 11),
            (2018, 05, 21),
            (2018, 10, 03),
            (2018, 12, 24),
            (2018, 12, 25),
            (2018, 12, 26),
            (2018, 12, 31),
            (2019, 01, 01),
            (2019, 03, 08),
            (2019, 04, 19),
            (2019, 04, 22),
            (2019, 05, 01),
            (2019, 05, 30),
            (2019, 05, 31),
            (2019, 06, 10),
            (2019, 10, 03),
            (2019, 12, 24),
            (2019, 12, 25),
            (2019, 12, 26),
            (2019, 12, 31),
            (2020, 01, 01),
            (2020, 03, 08),
            (2020, 04, 10),
            (2020, 04, 13),
            (2020, 05, 01),
            (2020, 05, 21),
            (2020, 05, 22),
            (2020, 06, 01),
            (2020, 10, 03),
            (2020, 12, 24),
            (2020, 12, 25),
            (2020, 12, 26),
            (2020, 12, 31)
        ');

        $this->addSql('ALTER TABLE app_user_year_stats ADD CONSTRAINT FK_994AA18BB827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_hours ADD CONSTRAINT FK_A2E1C6A2BB827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A2E1C6A2BB827337 ON work_hours (year)');
        $this->addSql('ALTER TABLE work_hours DROP CONSTRAINT work_hours_pkey');
        $this->addSql('ALTER TABLE work_hours ADD PRIMARY KEY (month, user_id, year)');
        $this->addSql('ALTER TABLE work_month ADD CONSTRAINT FK_A64D6B29BB827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A64D6B29BB827337 ON work_month (year)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE app_user_year_stats DROP CONSTRAINT FK_994AA18BB827337');
        $this->addSql('ALTER TABLE supported_holiday DROP CONSTRAINT FK_E375C161BB827337');
        $this->addSql('ALTER TABLE work_hours DROP CONSTRAINT FK_A2E1C6A2BB827337');
        $this->addSql('ALTER TABLE work_month DROP CONSTRAINT FK_A64D6B29BB827337');
        $this->addSql('DROP TABLE supported_holiday');
        $this->addSql('DROP TABLE supported_year');
        $this->addSql('DROP INDEX IDX_A2E1C6A2BB827337');
        $this->addSql('DROP INDEX work_hours_pkey');
        $this->addSql('ALTER TABLE work_hours ADD PRIMARY KEY (month, year, user_id)');
        $this->addSql('DROP INDEX IDX_A64D6B29BB827337');
    }
}
