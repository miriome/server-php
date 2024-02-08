<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AndroidPushToken extends Migration
{
    public function up()
    {
        // Add 'platform' column with type string
        $this->forge->addColumn('device', [
            'platform' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'ios'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('device', 'platform');

    }
}
