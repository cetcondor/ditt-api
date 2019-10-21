<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191015125415 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE app_user_notifications (id SERIAL NOT NULL, user_id INT NOT NULL, supervisor_info_monday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_tuesday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_wednesday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_thursday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_friday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_saturday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_sunday_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, supervisor_info_send_on_holidays BOOLEAN NOT NULL, supervisor_info_last_notification_date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE9FD6FBA76ED395 ON app_user_notifications (user_id)');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_monday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_tuesday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_wednesday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_thursday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_friday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_saturday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_sunday_time IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user_notifications.supervisor_info_last_notification_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE app_user_notifications ADD CONSTRAINT FK_EE9FD6FBA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('
            DO
            $do$
            DECLARE
                appUser record;
            BEGIN
                FOR appUser IN (SELECT * FROM app_user)
                LOOP
                    INSERT INTO app_user_notifications (user_id, supervisor_info_monday_time, supervisor_info_tuesday_time, supervisor_info_wednesday_time, supervisor_info_thursday_time, supervisor_info_friday_time, supervisor_info_send_on_holidays) VALUES (appUser.id, \'8:00\', \'8:00\', \'8:00\', \'8:00\', \'8:00\', false);
                END LOOP;
            END;
            $do$;
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE app_user_notifications');
    }
}
