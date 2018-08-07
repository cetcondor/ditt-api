<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180807092208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sick_day_work_log ADD child_name TEXT NULL');
        $this->addSql('ALTER TABLE sick_day_work_log ADD child_date_of_birth TIMESTAMP(0) WITHOUT TIME ZONE NULL');
        $this->addSql('COMMENT ON COLUMN sick_day_work_log.child_date_of_birth IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sick_day_work_log DROP child_name');
        $this->addSql('ALTER TABLE sick_day_work_log DROP child_date_of_birth');
    }
}
