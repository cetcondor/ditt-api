<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180625150258 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE business_trip_work_log ADD rejection_message TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE time_off_work_log ADD rejection_message TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_office_work_log ADD rejection_message TEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE business_trip_work_log DROP rejection_message');
        $this->addSql('ALTER TABLE time_off_work_log DROP rejection_message');
        $this->addSql('ALTER TABLE home_office_work_log DROP rejection_message');
    }
}
