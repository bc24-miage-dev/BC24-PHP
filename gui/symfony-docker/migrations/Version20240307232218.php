<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240307232218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER NOT NULL, origin_id INTEGER NOT NULL, resource_name VARCHAR(100) NOT NULL, is_final_product BOOLEAN NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date) SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_research AS SELECT id, date FROM user_research');
        $this->addSql('DROP TABLE user_research');
        $this->addSql('CREATE TABLE user_research (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, resource_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, CONSTRAINT FK_B766B4C2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B766B4C289329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_research (id, date) SELECT id, date FROM __temp__user_research');
        $this->addSql('DROP TABLE __temp__user_research');
        $this->addSql('CREATE INDEX IDX_B766B4C2A76ED395 ON user_research (user_id)');
        $this->addSql('CREATE INDEX IDX_B766B4C289329D25 ON user_research (resource_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_role_request AS SELECT id, id_user_id, role_request, date_role_request, read, description FROM user_role_request');
        $this->addSql('DROP TABLE user_role_request');
        $this->addSql('CREATE TABLE user_role_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, role_request VARCHAR(255) NOT NULL, date_role_request DATETIME NOT NULL, read BOOLEAN NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_7965666A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_role_request (id, user_id, role_request, date_role_request, read, description) SELECT id, id_user_id, role_request, date_role_request, read, description FROM __temp__user_role_request');
        $this->addSql('DROP TABLE __temp__user_role_request');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7965666A76ED395 ON user_role_request (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, resource_name VARCHAR(100) NOT NULL, is_final_product BOOLEAN NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, date DATETIME DEFAULT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date) SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description, date FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_research AS SELECT id, date FROM user_research');
        $this->addSql('DROP TABLE user_research');
        $this->addSql('CREATE TABLE user_research (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_user_id INTEGER DEFAULT NULL, id_resource_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, CONSTRAINT FK_B766B4C279F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B766B4C2912050D2 FOREIGN KEY (id_resource_id) REFERENCES resource (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_research (id, date) SELECT id, date FROM __temp__user_research');
        $this->addSql('DROP TABLE __temp__user_research');
        $this->addSql('CREATE INDEX IDX_B766B4C2912050D2 ON user_research (id_resource_id)');
        $this->addSql('CREATE INDEX IDX_B766B4C279F37AE5 ON user_research (id_user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_role_request AS SELECT id, user_id, role_request, date_role_request, read, description FROM user_role_request');
        $this->addSql('DROP TABLE user_role_request');
        $this->addSql('CREATE TABLE user_role_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_user_id INTEGER NOT NULL, role_request VARCHAR(255) NOT NULL, date_role_request DATETIME NOT NULL, read BOOLEAN NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_796566679F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_role_request (id, id_user_id, role_request, date_role_request, read, description) SELECT id, user_id, role_request, date_role_request, read, description FROM __temp__user_role_request');
        $this->addSql('DROP TABLE __temp__user_role_request');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_796566679F37AE5 ON user_role_request (id_user_id)');
    }
}
