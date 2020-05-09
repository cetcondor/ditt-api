<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200430071217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add work log support classes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE time_off_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27EFB269A76ED395 ON time_off_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_27EFB269419E9BA4 ON time_off_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN time_off_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE vacation_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_50430A9FA76ED395 ON vacation_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_50430A9F419E9BA4 ON vacation_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN vacation_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE business_trip_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3B936F6A76ED395 ON business_trip_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_A3B936F6419E9BA4 ON business_trip_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN business_trip_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE special_leave_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7EE054FFA76ED395 ON special_leave_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_7EE054FF419E9BA4 ON special_leave_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN special_leave_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE home_office_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EB540ADCA76ED395 ON home_office_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_EB540ADC419E9BA4 ON home_office_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN home_office_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE overtime_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3B8186E6A76ED395 ON overtime_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_3B8186E6419E9BA4 ON overtime_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN overtime_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE time_off_work_log_support ADD CONSTRAINT FK_27EFB269A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_off_work_log_support ADD CONSTRAINT FK_27EFB269419E9BA4 FOREIGN KEY (work_log_id) REFERENCES time_off_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation_work_log_support ADD CONSTRAINT FK_50430A9FA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation_work_log_support ADD CONSTRAINT FK_50430A9F419E9BA4 FOREIGN KEY (work_log_id) REFERENCES vacation_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_trip_work_log_support ADD CONSTRAINT FK_A3B936F6A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_trip_work_log_support ADD CONSTRAINT FK_A3B936F6419E9BA4 FOREIGN KEY (work_log_id) REFERENCES business_trip_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE special_leave_work_log_support ADD CONSTRAINT FK_7EE054FFA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE special_leave_work_log_support ADD CONSTRAINT FK_7EE054FF419E9BA4 FOREIGN KEY (work_log_id) REFERENCES special_leave_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE home_office_work_log_support ADD CONSTRAINT FK_EB540ADCA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE home_office_work_log_support ADD CONSTRAINT FK_EB540ADC419E9BA4 FOREIGN KEY (work_log_id) REFERENCES home_office_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE overtime_work_log_support ADD CONSTRAINT FK_3B8186E6A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE overtime_work_log_support ADD CONSTRAINT FK_3B8186E6419E9BA4 FOREIGN KEY (work_log_id) REFERENCES overtime_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE time_off_work_log_support');
        $this->addSql('DROP TABLE vacation_work_log_support');
        $this->addSql('DROP TABLE business_trip_work_log_support');
        $this->addSql('DROP TABLE special_leave_work_log_support');
        $this->addSql('DROP TABLE home_office_work_log_support');
        $this->addSql('DROP TABLE overtime_work_log_support');
    }
}
