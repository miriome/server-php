<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMentions extends Migration
{
    public function up()
    {
        // Create post mentions
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'post_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'username' => array(
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ),
            // Add other fields as needed
        );
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('posts_mentions');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE posts_mentions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE posts_mentions ADD FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Create comment mentions
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'comment_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'username' => array(
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ),
            // Add other fields as needed
        );
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('comments_mentions');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE comments_mentions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE comments_mentions ADD FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {

        $this->forge->dropTable('posts_mentions');
        $this->forge->dropTable('comments_mentions');
    }
}
