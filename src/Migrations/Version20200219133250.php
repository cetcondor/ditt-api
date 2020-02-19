<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200219133250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add parental leave work log';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE parental_leave_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7E417DAADFB937B8 ON parental_leave_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN parental_leave_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE parental_leave_work_log ADD CONSTRAINT FK_7E417DAADFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE parental_leave_work_log');
    }
}
