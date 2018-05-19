<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180522130029 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user ADD supervisor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E919E9AC5F FOREIGN KEY (supervisor_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_88BDF3E919E9AC5F ON app_user (supervisor_id)');
        // password = password
        $this->addSql('INSERT INTO app_user (email, first_name, last_name, password, roles, is_active) VALUES(\'employee-admin@example.com\', \'Johny\', \'Morty\', \'$2a$04$b5vig7mulvf4qWSVncS6me2hXww.h.9oGOWadhP.91jiGuDrLjxyW\', \'["ROLE_EMPLOYEE", "ROLE_ADMIN"]\', TRUE)');
        $this->addSql('INSERT INTO app_user (email, first_name, last_name, password, roles, is_active) VALUES(\'admin@example.com\', \'Adam\', \'Rock\', \'$2a$04$b5vig7mulvf4qWSVncS6me2hXww.h.9oGOWadhP.91jiGuDrLjxyW\', \'["ROLE_ADMIN"]\', TRUE)');
        $this->addSql('INSERT INTO app_user (email, first_name, last_name, password, roles, is_active) VALUES(\'superadmin@example.com\', \'Jackob\', \'Malcolm\', \'$2a$04$b5vig7mulvf4qWSVncS6me2hXww.h.9oGOWadhP.91jiGuDrLjxyW\', \'["ROLE_SUPER_ADMIN"]\', TRUE)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE app_user DROP CONSTRAINT FK_88BDF3E919E9AC5F');
        $this->addSql('DROP INDEX IDX_88BDF3E919E9AC5F');
        $this->addSql('ALTER TABLE app_user DROP supervisor_id');
    }
}
