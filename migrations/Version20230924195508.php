<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230924195508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE men_users ADD college_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE men_users ADD CONSTRAINT FK_27DE03EC770124B2 FOREIGN KEY (college_id) REFERENCES `men_colleges` (id)');
        $this->addSql('CREATE INDEX IDX_27DE03EC770124B2 ON men_users (college_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `men_users` DROP FOREIGN KEY FK_27DE03EC770124B2');
        $this->addSql('DROP INDEX IDX_27DE03EC770124B2 ON `men_users`');
        $this->addSql('ALTER TABLE `men_users` DROP college_id');
    }
}
