<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408084019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE element (id INT AUTO_INCREMENT NOT NULL, carton_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, in_box TINYINT(1) DEFAULT NULL, out_box TINYINT(1) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_41405E392D79FBB1 (carton_id), INDEX IDX_41405E39A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE element ADD CONSTRAINT FK_41405E392D79FBB1 FOREIGN KEY (carton_id) REFERENCES carton (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE element ADD CONSTRAINT FK_41405E39A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE element DROP FOREIGN KEY FK_41405E392D79FBB1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE element DROP FOREIGN KEY FK_41405E39A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE element
        SQL);
    }
}
