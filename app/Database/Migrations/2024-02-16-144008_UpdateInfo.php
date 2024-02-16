<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateInfo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'force' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('update_info');
    }

    public function down()
    {
        $this->forge->dropTable('update_info');
    }
}
