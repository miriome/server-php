<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MultiplePostImages extends Migration
{
    public function up()
    {
        // Create post images
        $fields = array(
            'post_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'index' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'image' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            // Add other fields as needed
        );
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey(['post_id', 'index']);
        $this->forge->createTable('post_images');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE post_images ADD FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE');

    }

    public function down()
    {
        $this->forge->dropTable('post_images');
    }
}
