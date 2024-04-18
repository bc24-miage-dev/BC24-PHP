<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313231129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE production_site ADD COLUMN validate BOOLEAN DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_role_request AS SELECT id, user_id, role_request, date_role_request, read, description FROM user_role_request');
        $this->addSql('DROP TABLE user_role_request');
        $this->addSql('CREATE TABLE user_role_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, production_site_id INTEGER DEFAULT NULL, role_request VARCHAR(255) NOT NULL, date_role_request DATETIME NOT NULL, read BOOLEAN NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_7965666A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7965666E3A89B9B FOREIGN KEY (production_site_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_role_request (id, user_id, role_request, date_role_request, read, description) SELECT id, user_id, role_request, date_role_request, read, description FROM __temp__user_role_request');
        $this->addSql('DROP TABLE __temp__user_role_request');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7965666A76ED395 ON user_role_request (user_id)');
        $this->addSql('CREATE INDEX IDX_7965666E3A89B9B ON user_role_request (production_site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__production_site AS SELECT id, production_site_name, address, production_site_tel FROM production_site');
        $this->addSql('DROP TABLE production_site');
        $this->addSql('CREATE TABLE production_site (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, production_site_name VARCHAR(100) NOT NULL, address VARCHAR(255) NOT NULL, production_site_tel VARCHAR(15) NOT NULL)');
        $this->addSql('INSERT INTO production_site (id, production_site_name, address, production_site_tel) SELECT id, production_site_name, address, production_site_tel FROM __temp__production_site');
        $this->addSql('DROP TABLE __temp__production_site');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_role_request AS SELECT id, user_id, role_request, date_role_request, read, description FROM user_role_request');
        $this->addSql('DROP TABLE user_role_request');
        $this->addSql('CREATE TABLE user_role_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, role_request VARCHAR(255) NOT NULL, date_role_request DATETIME NOT NULL, read BOOLEAN NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_7965666A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_role_request (id, user_id, role_request, date_role_request, read, description) SELECT id, user_id, role_request, date_role_request, read, description FROM __temp__user_role_request');
        $this->addSql('DROP TABLE __temp__user_role_request');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7965666A76ED395 ON user_role_request (user_id)');
    }
}
