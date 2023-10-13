<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeviceTable extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id'            => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_push_token'      => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'uid' => [
                'type' => 'INT',
                'constraint' => 11,
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('uid', 'users', 'id', 'CASCADE', 'CASCADE');         
        $this->forge->createTable('device');
    }

    public function down()
    {
        //
        $this->forge->dropTable('device');
    }
}
