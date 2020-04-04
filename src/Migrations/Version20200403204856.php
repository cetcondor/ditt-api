<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200403204856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add createdOn field to sick day work log';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sick_day_work_log ADD created_on TIMESTAMP(0) WITHOUT TIME ZONE NULL');
        $this->addSql('UPDATE sick_day_work_log SET created_on = sick_day_work_log.date');
        $this->addSql('ALTER TABLE sick_day_work_log ALTER COLUMN created_on SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN sick_day_work_log.created_on IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sick_day_work_log DROP created_on');
    }
}
