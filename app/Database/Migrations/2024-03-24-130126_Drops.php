<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Drops extends Migration
{
    public function up()
    {
        // Create drop
        $fields = array(
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'startTimestamp' => array(
                'type' => 'TIMESTAMP',
            ),
            'endTimestamp' => array(
                'type' => 'TIMESTAMP',
            ),
        );

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('drops');

        // Create drop post
        $fields = array(
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'caption' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'condition' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'isSold' => array(
                'type' => 'BOOLEAN',
                'default' => false,
            ),
            'clothingSize' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'price' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'isPetite' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'dropId' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ),

        );
        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('drop_posts');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE drop_posts ADD FOREIGN KEY (dropId) REFERENCES drops(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Create drop post images
        $fields = array(
            'dropPostId' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
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
        $this->forge->addPrimaryKey(['dropPostId', 'index']);
        $this->forge->createTable('drop_post_images');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE drop_post_images ADD FOREIGN KEY (dropPostId) REFERENCES drop_posts(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        //
        $this->forge->dropTable('drops');
        $this->forge->dropTable('drop_posts');
        $this->forge->dropTable('drop_post_images');
    }
}
