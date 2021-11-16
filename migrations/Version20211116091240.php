<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211116091240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE downvote');
        $this->addSql('DROP TABLE upvote');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE downvote (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, post_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, INDEX IDX_70CE3094B89032C (post_id), INDEX IDX_70CE309A76ED395 (user_id), INDEX IDX_70CE309F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE upvote (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, post_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, INDEX IDX_68AB87664B89032C (post_id), INDEX IDX_68AB8766A76ED395 (user_id), INDEX IDX_68AB8766F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE downvote ADD CONSTRAINT FK_70CE3094B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE downvote ADD CONSTRAINT FK_70CE309A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE downvote ADD CONSTRAINT FK_70CE309F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE upvote ADD CONSTRAINT FK_68AB87664B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE upvote ADD CONSTRAINT FK_68AB8766A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE upvote ADD CONSTRAINT FK_68AB8766F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
    }
}
