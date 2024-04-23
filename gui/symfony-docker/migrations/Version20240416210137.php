<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416210137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, genre VARCHAR(8) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');

        $this->addSql("DROP TABLE IF EXISTS resource_category");
        $this->addSql("CREATE TABLE IF NOT EXISTS resource_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category VARCHAR(30) NOT NULL)");
        $this->addSql("DROP TABLE IF EXISTS resource_family");
        $this->addSql("CREATE TABLE IF NOT EXISTS resource_family (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)");


        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, genre VARCHAR(8) DEFAULT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
    }
}
