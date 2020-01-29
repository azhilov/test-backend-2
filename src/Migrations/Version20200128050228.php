<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200128050228 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE links (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'ИД ссылки\', user_id INT NOT NULL, url VARCHAR(1000) NOT NULL COMMENT \'Адрес ссылки\', code VARCHAR(255) NOT NULL COMMENT \'Код ссылки\', short_url VARCHAR(255) NOT NULL COMMENT \'Короткая ссылка\', category VARCHAR(255) NOT NULL COMMENT \'Категория ссылки\', counter INT UNSIGNED DEFAULT 0 NOT NULL COMMENT \'Счётчик переходов по ссылке\', updated_at DATETIME NOT NULL COMMENT \'Дата обновления ссылки\', INDEX IDX_D182A118A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE links ADD CONSTRAINT FK_D182A118A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE links DROP FOREIGN KEY FK_D182A118A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE links');
    }
}
