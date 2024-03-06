<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222155354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_resource (resource_source INTEGER NOT NULL, resource_target INTEGER NOT NULL, PRIMARY KEY(resource_source, resource_target), CONSTRAINT FK_121D3EBB97769589 FOREIGN KEY (resource_source) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_121D3EBB8E93C506 FOREIGN KEY (resource_target) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_121D3EBB97769589 ON resource_resource (resource_source)');
        $this->addSql('CREATE INDEX IDX_121D3EBB8E93C506 ON resource_resource (resource_target)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, resource_name VARCHAR(100) NOT NULL, is_final_product BOOLEAN NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description) SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE resource_resource');
        $this->addSql('CREATE TEMPORARY TABLE __temp__resource AS SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description FROM resource');
        $this->addSql('DROP TABLE resource');
        $this->addSql('CREATE TABLE resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, origin_id INTEGER NOT NULL, resource_id INTEGER DEFAULT NULL, resource_name VARCHAR(100) NOT NULL, is_final_product BOOLEAN NOT NULL, is_contamined BOOLEAN NOT NULL, weight DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_BC91F41656A273CC FOREIGN KEY (origin_id) REFERENCES production_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC91F41689329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO resource (id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description) SELECT id, origin_id, resource_name, is_final_product, is_contamined, weight, price, description FROM __temp__resource');
        $this->addSql('DROP TABLE __temp__resource');
        $this->addSql('CREATE INDEX IDX_BC91F41656A273CC ON resource (origin_id)');
        $this->addSql('CREATE INDEX IDX_BC91F41689329D25 ON resource (resource_id)');
    }
}
