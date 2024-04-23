<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322101333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_name (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resource_category_id INTEGER NOT NULL, family_id INTEGER DEFAULT NULL, production_site_owner_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_5103DEBC16FDA3B0 FOREIGN KEY (resource_category_id) REFERENCES resource_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCC35E566A FOREIGN KEY (family_id) REFERENCES resource_family (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCE88D63C7 FOREIGN KEY (production_site_owner_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5103DEBCC35E566A ON resource_name (family_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBC16FDA3B0 ON resource_name (resource_category_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBCE88D63C7 ON resource_name (production_site_owner_id)');

        $this->addSql('CREATE TABLE resource_name_resource_name (resource_name_source INTEGER NOT NULL, resource_name_target INTEGER NOT NULL, PRIMARY KEY(resource_name_source, resource_name_target), CONSTRAINT FK_61AB6A2B5D49D26C FOREIGN KEY (resource_name_source) REFERENCES resource_name (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_61AB6A2B44AC82E3 FOREIGN KEY (resource_name_target) REFERENCES resource_name (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_61AB6A2B5D49D26C ON resource_name_resource_name (resource_name_source)');
        $this->addSql('CREATE INDEX IDX_61AB6A2B44AC82E3 ON resource_name_resource_name (resource_name_target)');
        $this->addSql('DROP TABLE factory_recipe');
        $this->addSql('DROP TABLE factory_recipe_resource_name');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE factory_recipe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, factory_owner_id INTEGER NOT NULL, recipe_name_id INTEGER NOT NULL, CONSTRAINT FK_948796D5B77E0BF3 FOREIGN KEY (factory_owner_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_948796D53DF39296 FOREIGN KEY (recipe_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_948796D53DF39296 ON factory_recipe (recipe_name_id)');
        $this->addSql('CREATE INDEX IDX_948796D5B77E0BF3 ON factory_recipe (factory_owner_id)');
        $this->addSql('CREATE TABLE factory_recipe_resource_name (factory_recipe_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, PRIMARY KEY(factory_recipe_id, resource_name_id), CONSTRAINT FK_17918C29B9C40960 FOREIGN KEY (factory_recipe_id) REFERENCES factory_recipe (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_17918C292CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_17918C292CB77B3E ON factory_recipe_resource_name (resource_name_id)');
        $this->addSql('CREATE INDEX IDX_17918C29B9C40960 ON factory_recipe_resource_name (factory_recipe_id)');
        $this->addSql('DROP TABLE resource_name_resource_name');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource_name AS SELECT id, resource_category_id, family_id, name FROM resource_name');
        $this->addSql('DROP TABLE resource_name');
        $this->addSql('CREATE TABLE resource_name (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resource_category_id INTEGER NOT NULL, family_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_5103DEBC16FDA3B0 FOREIGN KEY (resource_category_id) REFERENCES resource_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5103DEBCC35E566A FOREIGN KEY (family_id) REFERENCES resource_family (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource_name (id, resource_category_id, family_id, name) SELECT id, resource_category_id, family_id, name FROM __temp__resource_name');
        $this->addSql('DROP TABLE __temp__resource_name');
        $this->addSql('CREATE INDEX IDX_5103DEBC16FDA3B0 ON resource_name (resource_category_id)');
        $this->addSql('CREATE INDEX IDX_5103DEBCC35E566A ON resource_name (family_id)');
    }
}
