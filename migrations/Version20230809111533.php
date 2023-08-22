<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230809111533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE men_rapports CHANGE activite_fichier activite_fichier LONGTEXT DEFAULT NULL, CHANGE description_fichier description_fichier LONGTEXT DEFAULT NULL, CHANGE resultat_fichier resultat_fichier LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `men_rapports` CHANGE activite_fichier activite_fichier LONGBLOB DEFAULT NULL, CHANGE description_fichier description_fichier LONGBLOB DEFAULT NULL, CHANGE resultat_fichier resultat_fichier LONGBLOB DEFAULT NULL');
    }
}
