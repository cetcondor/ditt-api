<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180629134011 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE sick_day_work_log (id SERIAL NOT NULL, work_month_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, variant TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_296D4043DFB937B8 ON sick_day_work_log (work_month_id)');
        $this->addSql('COMMENT ON COLUMN sick_day_work_log.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE sick_day_work_log ADD CONSTRAINT FK_296D4043DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE sick_day_work_log');
    }
}
