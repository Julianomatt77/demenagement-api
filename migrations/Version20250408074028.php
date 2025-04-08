<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408074028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carton (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, user_id INT NOT NULL, numero INT NOT NULL, filled TINYINT(1) DEFAULT NULL, items_removed TINYINT(1) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_4151110654177093 (room_id), INDEX IDX_41511106A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carton ADD CONSTRAINT FK_4151110654177093 FOREIGN KEY (room_id) REFERENCES room (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carton ADD CONSTRAINT FK_41511106A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE carton DROP FOREIGN KEY FK_4151110654177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carton DROP FOREIGN KEY FK_41511106A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE carton
        SQL);
    }
}
