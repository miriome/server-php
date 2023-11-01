<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSearchTermTable extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'base_term' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'mapped_term' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
        ];
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('search_terms', TRUE);
    }

    public function down()
    {
        //
        $this->forge->dropTable('search_terms');
    }
}
