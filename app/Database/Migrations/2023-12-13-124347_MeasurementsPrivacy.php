<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeasurementsPrivacy extends Migration
{
    public function up()
    {
        // Add a string column to the 'your_table_name' table with a default value of 'all'
        $fields = [
            'measurement_privacy' => [
                'type' => 'VARCHAR',
                'constraint' => 100, // Adjust the length according to your requirements
                'default' => 'all',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        // Drop the added column if you need to rollback
        $this->forge->dropColumn('users', 'measurement_privacy');
    }
}
