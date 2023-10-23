<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportedContentTable extends Migration
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
            'report_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'reported_by' => [
                'type'       => 'INT',
                'constraint' => 11,
            ]
        ];
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('reported_by', 'users', 'id', false, 'CASCADE');
        $this->forge->createTable('reports', TRUE);

        $this->db->query('ALTER TABLE `reports` ADD UNIQUE `unique_index`(`report_type`, `type_id`, `reported_by`)');


    }

    public function down()
    {
        //
        $this->forge->dropTable('reports', TRUE, TRUE);
    }
}
