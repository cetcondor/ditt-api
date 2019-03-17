<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190317120045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE vacation (user_id INT NOT NULL, year INT NOT NULL, vacation_days INT NOT NULL, PRIMARY KEY(user_id, year))');
        $this->addSql('CREATE INDEX IDX_E3DADF75A76ED395 ON vacation (user_id)');
        $this->addSql('CREATE INDEX IDX_E3DADF75BB827337 ON vacation (year)');
        $this->addSql('ALTER TABLE vacation ADD CONSTRAINT FK_E3DADF75A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation ADD CONSTRAINT FK_E3DADF75BB827337 FOREIGN KEY (year) REFERENCES supported_year (year) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('
            DO
            $do$
            DECLARE
                appUser record;
                supportedYear record;
            BEGIN
                FOR appUser IN (SELECT * FROM app_user)
                LOOP
                    FOR supportedYear IN (SELECT * FROM supported_year)
                    LOOP
                        INSERT INTO vacation (user_id, year, vacation_days) VALUES (appUser.id, supportedYear.year, appUser.vacation_days);
                    END LOOP;
                END LOOP;
            END;
            $do$;
        ');
        $this->addSql('ALTER TABLE app_user DROP vacation_days');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE vacation');
        $this->addSql('ALTER TABLE app_user ADD vacation_days INT NOT NULL');
    }
}
