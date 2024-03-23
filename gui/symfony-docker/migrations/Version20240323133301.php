<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240323133301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__recipe AS SELECT id, recipe_title_id FROM recipe');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('CREATE TABLE recipe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, recipe_title_id INTEGER NOT NULL, ingredient_id INTEGER NOT NULL, ingredient_number INTEGER NOT NULL, CONSTRAINT FK_DA88B1377C0F2223 FOREIGN KEY (recipe_title_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DA88B137933FE08C FOREIGN KEY (ingredient_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO recipe (id, recipe_title_id) SELECT id, recipe_title_id FROM __temp__recipe');
        $this->addSql('DROP TABLE __temp__recipe');
        $this->addSql('CREATE INDEX IDX_DA88B1377C0F2223 ON recipe (recipe_title_id)');
        $this->addSql('CREATE INDEX IDX_DA88B137933FE08C ON recipe (ingredient_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__recipe AS SELECT id, recipe_title_id FROM recipe');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('CREATE TABLE recipe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, recipe_title_id INTEGER NOT NULL, CONSTRAINT FK_DA88B1377C0F2223 FOREIGN KEY (recipe_title_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO recipe (id, recipe_title_id) SELECT id, recipe_title_id FROM __temp__recipe');
        $this->addSql('DROP TABLE __temp__recipe');
        $this->addSql('CREATE INDEX IDX_DA88B1377C0F2223 ON recipe (recipe_title_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, current_owner_id INTEGER NOT NULL, resource_name_id INTEGER NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, is_life_cycle_over BOOLEAN NOT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F416E3441BD3 FOREIGN KEY (current_owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F4162CB77B3E FOREIGN KEY (resource_name_id) REFERENCES resource_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over) SELECT id, origin_id, current_owner_id, resource_name_id, is_contamined, weight, price, description, date, is_life_cycle_over FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F416E3441BD3 ON resource (current_owner_id)');
        $this->addSql('CREATE INDEX IDX_BC91F4162CB77B3E ON resource (resource_name_id)');
    }
}
