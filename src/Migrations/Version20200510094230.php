<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200510094230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change required and worked hours from hour precision to second precision';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE app_user_year_stats SET required_hours = required_hours * 3600');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER required_hours TYPE INT');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER required_hours DROP DEFAULT');
        $this->addSql('UPDATE app_user_year_stats SET worked_hours = worked_hours * 3600');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER worked_hours TYPE INT');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER worked_hours DROP DEFAULT');
        $this->addSql('UPDATE work_hours SET required_hours = required_hours * 3600');
        $this->addSql('ALTER TABLE work_hours ALTER required_hours TYPE INT');
        $this->addSql('ALTER TABLE work_hours ALTER required_hours DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user_year_stats ALTER required_hours TYPE DOUBLE PRECISION');
        $this->addSql('UPDATE app_user_year_stats SET required_hours = required_hours / 3600');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER required_hours DROP DEFAULT');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER worked_hours TYPE DOUBLE PRECISION');
        $this->addSql('UPDATE app_user_year_stats SET worked_hours = worked_hours / 3600');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER worked_hours DROP DEFAULT');
        $this->addSql('ALTER TABLE work_hours ALTER required_hours TYPE DOUBLE PRECISION');
        $this->addSql('UPDATE work_hours SET required_hours = required_hours / 3600');
        $this->addSql('ALTER TABLE work_hours ALTER required_hours DROP DEFAULT');
    }
}
