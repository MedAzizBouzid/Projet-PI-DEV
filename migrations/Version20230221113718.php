<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230221113718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pass DROP FOREIGN KEY FK_CE70D42471F7E88B');
        $this->addSql('ALTER TABLE pass ADD CONSTRAINT FK_CE70D42471F7E88B FOREIGN KEY (event_id) REFERENCES evenement (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pass DROP FOREIGN KEY FK_CE70D42471F7E88B');
        $this->addSql('ALTER TABLE pass ADD CONSTRAINT FK_CE70D42471F7E88B FOREIGN KEY (event_id) REFERENCES evenement (id)');
    }
}
