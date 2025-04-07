<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407121119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE demenageur (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, devis_reference VARCHAR(255) DEFAULT NULL, devis_price INT DEFAULT NULL, paid INT DEFAULT NULL, left_to_paid INT DEFAULT NULL, devis_date DATE DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_2D10B5BAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demenageur ADD CONSTRAINT FK_2D10B5BAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE demenageur DROP FOREIGN KEY FK_2D10B5BAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demenageur
        SQL);
    }
}
