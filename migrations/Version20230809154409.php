<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230809154409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE men_rapports ADD college_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE men_rapports ADD CONSTRAINT FK_945D03A770124B2 FOREIGN KEY (college_id) REFERENCES `men_colleges` (id)');
        $this->addSql('CREATE INDEX IDX_945D03A770124B2 ON men_rapports (college_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `men_rapports` DROP FOREIGN KEY FK_945D03A770124B2');
        $this->addSql('DROP INDEX IDX_945D03A770124B2 ON `men_rapports`');
        $this->addSql('ALTER TABLE `men_rapports` DROP college_id');
    }
}
