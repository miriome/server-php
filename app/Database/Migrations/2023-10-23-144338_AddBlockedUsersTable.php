<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlockedUsersTable extends Migration
{
    public function up()
    {
        //
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'blocked_by' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ];
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('blocked_by', 'users', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
        $this->forge->createTable('blocked_users', TRUE);
    }

    public function down()
    {
        //
        $this->forge->dropTable('blocked_users');
    }
}
