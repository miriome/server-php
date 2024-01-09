<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FakeViews extends Migration
{
    public function up()
    {
        // Add 'views' column with type int64 and default value 0
        $this->forge->addColumn('posts', [
            'views' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'default' => 0,
            ],
        ]);

        // Add 'fake_views' column with type double
        $this->forge->addColumn('posts', [
            'views_multiplier' => [
                'type' => 'DOUBLE',
                'default' => 1 + 2*mt_rand(1, 100) / 100
            ],
        ]);
    }

    public function down()
    {
        // Remove 'views' and 'fake_views' columns
        $this->forge->dropColumn('posts', 'views');
        $this->forge->dropColumn('posts', 'fake_views');
    }
}
