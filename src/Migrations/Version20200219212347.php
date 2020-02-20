<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200219212347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add special leave work log';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE special_leave_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, time_approved TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_rejected TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejection_message TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2BD5FA9ADFB937B8 ON special_leave_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN special_leave_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN special_leave_work_log.time_approved IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN special_leave_work_log.time_rejected IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE special_leave_work_log ADD CONSTRAINT FK_2BD5FA9ADFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE special_leave_work_log');
    }
}
