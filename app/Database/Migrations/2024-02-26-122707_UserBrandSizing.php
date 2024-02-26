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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
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
        // Add foreign key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_brand_sizing');
    }

    public function down()
    {
        $this->forge->dropTable('user_brand_sizing');
    }
}
