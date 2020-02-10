<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180512093841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE app_user (id SERIAL NOT NULL, email TEXT NOT NULL, first_name TEXT NOT NULL, last_name TEXT NOT NULL, password TEXT NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9E7927C74 ON app_user (email)');
        $this->addSql('COMMENT ON COLUMN app_user.roles IS \'(DC2Type:json_array)\'');
        // password = password
        $this->addSql('INSERT INTO app_user (email, first_name, last_name, password, roles) VALUES(\'employee@example.com\', \'Jim\', \'Smith\', \'$2a$04$b5vig7mulvf4qWSVncS6me2hXww.h.9oGOWadhP.91jiGuDrLjxyW\', \'["ROLE_EMPLOYEE"]\')');
        $this->addSql('ALTER TABLE work_log ADD user_id INT NULL');
        $this->addSql('UPDATE work_log SET user_id = (SELECT id FROM app_user WHERE email = \'employee@example.com\')');
        $this->addSql('ALTER TABLE work_log ALTER COLUMN user_id SET NOT NULL');
        $this->addSql('ALTER TABLE work_log ADD CONSTRAINT FK_F5513F59A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F5513F59A76ED395 ON work_log (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE work_log DROP CONSTRAINT FK_F5513F59A76ED395');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP INDEX IDX_F5513F59A76ED395');
        $this->addSql('ALTER TABLE work_log DROP user_id');
    }
}
