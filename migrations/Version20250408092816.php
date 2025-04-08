<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408092816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE administratif (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, company VARCHAR(255) NOT NULL, assigned_user VARCHAR(255) DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_done DATETIME DEFAULT NULL, comment LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_145F75AAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE administratif ADD CONSTRAINT FK_145F75AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE administratif DROP FOREIGN KEY FK_145F75AAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE administratif
        SQL);
    }
}
