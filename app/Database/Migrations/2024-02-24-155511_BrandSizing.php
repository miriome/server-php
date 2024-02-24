<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BrandSizing extends Migration
{
    public function up()
    {
        $fields = array(
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'brand' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'brand_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'clothing_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
            'size' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
            ),
        );
        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('brand_sizing');
    }

    public function down()
    {
        $this->forge->dropTable('brand_sizing');
    }
}
