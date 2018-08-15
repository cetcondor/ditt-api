<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180814141227 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user_year_stats ADD required_hours DOUBLE PRECISION NULL');
        $this->addSql('ALTER TABLE app_user_year_stats ADD worked_hours DOUBLE PRECISION NULL');
        $this->addSql('UPDATE app_user_year_stats SET required_hours = 0, worked_hours = 0');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER COLUMN required_hours SET NOT NULL');
        $this->addSql('ALTER TABLE app_user_year_stats ALTER COLUMN worked_hours SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user_year_stats DROP required_hours');
        $this->addSql('ALTER TABLE app_user_year_stats DROP worked_hours');
    }
}
