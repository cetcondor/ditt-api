<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201123313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE training_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title TEXT NOT NULL, comment TEXT DEFAULT NULL, time_approved TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_rejected TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejection_message TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12342CCEDFB937B8 ON training_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN training_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN training_work_log.time_approved IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN training_work_log.time_rejected IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE training_work_log_support (id SERIAL NOT NULL, user_id INT NOT NULL, work_log_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_16B68D6CA76ED395 ON training_work_log_support (user_id)');
        $this->addSql('CREATE INDEX IDX_16B68D6C419E9BA4 ON training_work_log_support (work_log_id)');
        $this->addSql('COMMENT ON COLUMN training_work_log_support.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE training_work_log ADD CONSTRAINT FK_12342CCEDFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE training_work_log_support ADD CONSTRAINT FK_16B68D6CA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE training_work_log_support ADD CONSTRAINT FK_16B68D6C419E9BA4 FOREIGN KEY (work_log_id) REFERENCES training_work_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE training_work_log_support DROP CONSTRAINT FK_16B68D6C419E9BA4');
        $this->addSql('DROP TABLE training_work_log');
        $this->addSql('DROP TABLE training_work_log_support');
    }
}
