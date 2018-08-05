<?php

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180805132126 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user ADD employee_id TEXT');
        $this->addSql('UPDATE app_user SET employee_id = email');
        $this->addSql('ALTER TABLE app_user ALTER COLUMN employee_id DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E98C03F15C ON app_user (employee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_88BDF3E98C03F15C');
        $this->addSql('ALTER TABLE app_user DROP employee_id');
    }
}
