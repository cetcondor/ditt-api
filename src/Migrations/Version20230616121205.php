<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616121205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_home_office to work_log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work_log ADD is_home_office BOOLEAN NULL');
        $this->addSql('UPDATE work_log SET is_home_office = FALSE');
        $this->addSql('ALTER TABLE work_log ALTER COLUMN is_home_office SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work_log DROP is_home_office');
    }
}
