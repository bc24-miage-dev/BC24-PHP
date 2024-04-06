<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240406183555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_name_resource_family (resource_name_id INTEGER NOT NULL, resource_family_id INTEGER NOT NULL, PRIMARY KEY(resource_name_id, resource_family_id), CONSTRAINT FK_9F1690412CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9F1690416B9705F5 FOREIGN KEY (resource_family_id) REFERENCES resource_family (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9F1690412CB77B3E ON resource_name_resource_family (resource_name_id)');
        $this->addSql('CREATE INDEX IDX_9F1690416B9705F5 ON resource_name_resource_family (resource_family_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, genre VARCHAR(8) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource_name AS SELECT id, resource_category_id, production_site_owner_id, name FROM resource_name');
        $this->addSql('DROP TABLE resource_name');
        $this->addSql('CREATE TABLE resource_name (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resource_category_id INTEGER NOT NULL, production_site_owner_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_5103DEBC16FDA3B0 FOREIGN KEY (resource_category_id) REFERENCES resource_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCE88D63C7 FOREIGN KEY (production_site_owner_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource_name (id, resource_category_id, production_site_owner_id, name) SELECT id, resource_category_id, production_site_owner_id, name FROM __temp__resource_name');
        $this->addSql('DROP TABLE __temp__resource_name');
        $this->addSql('CREATE INDEX IDX_5103DEBCE88D63C7 ON resource_name (production_site_owner_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBC16FDA3B0 ON resource_name (resource_category_id)');
        $this->addSql('ALTER TABLE user_role_request ADD COLUMN wallet_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE resource_name_resource_family');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, genre VARCHAR(8) DEFAULT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over, genre FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource_name AS SELECT id, resource_category_id, production_site_owner_id, name FROM resource_name');
        $this->addSql('DROP TABLE resource_name');
        $this->addSql('CREATE TABLE resource_name (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resource_category_id INTEGER NOT NULL, production_site_owner_id INTEGER DEFAULT NULL, family_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_5103DEBC16FDA3B0 FOREIGN KEY (resource_category_id) REFERENCES resource_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCE88D63C7 FOREIGN KEY (production_site_owner_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCC35E566A FOREIGN KEY (family_id) REFERENCES resource_family (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource_name (id, resource_category_id, production_site_owner_id, name) SELECT id, resource_category_id, production_site_owner_id, name FROM __temp__resource_name');
        $this->addSql('DROP TABLE __temp__resource_name');
        $this->addSql('CREATE INDEX IDX_5103DEBC16FDA3B0 ON resource_name (resource_category_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBCE88D63C7 ON resource_name (production_site_owner_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBCC35E566A ON resource_name (family_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_role_request AS SELECT id, user_id, production_site_id, role_request, date_role_request, read, description FROM user_role_request');
        $this->addSql('DROP TABLE user_role_request');
        $this->addSql('CREATE TABLE user_role_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, production_site_id INTEGER DEFAULT NULL, role_request VARCHAR(255) NOT NULL, date_role_request DATETIME NOT NULL, read BOOLEAN NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_7965666A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7965666E3A89B9B FOREIGN KEY (production_site_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_role_request (id, user_id, production_site_id, role_request, date_role_request, read, description) SELECT id, user_id, production_site_id, role_request, date_role_request, read, description FROM __temp__user_role_request');
        $this->addSql('DROP TABLE __temp__user_role_request');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7965666A76ED395 ON user_role_request (user_id)');
        $this->addSql('CREATE INDEX IDX_7965666E3A89B9B ON user_role_request (production_site_id)');
    }
}
