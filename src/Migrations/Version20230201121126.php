<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201121126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Quick fix to make tests working, production version needs to be fixed with https://github.com/DBJRdev/ditt-client/issues/111
        $this->addSql('
          INSERT INTO supported_year
              SELECT year FROM supported_year
              UNION
              VALUES (2021), (2022), (2023)
              EXCEPT
              SELECT year FROM supported_year');
    }

    public function down(Schema $schema): void
    {
    }
}
