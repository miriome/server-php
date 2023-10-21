<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPostAddedByColumnToForeignKey extends Migration
{
    public function up()
    {
        // Define the foreign key constraints
        $this->db->query('ALTER TABLE posts 
                          ADD CONSTRAINT FK_posts_users 
                          FOREIGN KEY (added_by) 
                          REFERENCES users(id) 
                          ON DELETE CASCADE ON UPDATE CASCADE');
        
        $this->db->query('ALTER TABLE comments 
                          ADD CONSTRAINT FK_comments_users 
                          FOREIGN KEY (user_id) 
                          REFERENCES users(id) 
                          ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE posts DROP FOREIGN KEY FK_posts_users');
        $this->db->query('ALTER TABLE posts DROP FOREIGN KEY FK_comments_users');
    }
}
