<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserBrandSizing extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'dropPostId' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'brand_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'clothing_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'size' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('drops_brand_sizing');
        // Add foreign key
        $this->db->query('ALTER TABLE drops_brand_sizing ADD FOREIGN KEY (dropPostId) REFERENCES drop_posts(id) ON DELETE CASCADE ON UPDATE CASCADE');


        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'height' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'bust' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'waist' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'hips' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'dropPostId' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('drops_body_sizing');
        // Add foreign key
        $this->db->query('ALTER TABLE drops_body_sizing ADD FOREIGN KEY (dropPostId) REFERENCES drop_posts(id) ON DELETE CASCADE ON UPDATE CASCADE');

    }

    public function down()
    {
        $this->forge->dropTable('drops_brand_sizing');
        $this->forge->dropTable('drops_body_sizing');
    }
}
