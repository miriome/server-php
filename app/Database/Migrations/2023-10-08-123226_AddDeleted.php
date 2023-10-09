<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeleted extends Migration
{   
    protected $DBGroup = 'default';
    public function up()
    {
        // Add deleted timestamp and is_deleted column
        $this->forge->addColumn('comments', ['deleted_timestamp' => [
            'type' => 'DATETIME',
            'null' => TRUE,
        ]]);
        $this->forge->addColumn('comments', ['is_deleted' => [
            'type' => 'BOOLEAN',
            'default' => 0
        ]]);
        
        // Trigger to update deleted timestamp.
        $sql = "
            CREATE TRIGGER add_deleted_timestamp BEFORE UPDATE ON comments
            FOR EACH ROW 
            BEGIN
                IF NEW.is_deleted = 1 AND OLD.is_deleted = 0 THEN
                    SET NEW.deleted_timestamp = NOW();
                END IF;
            END;
            ";

        $this->db->query($sql);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('comments', 'deleted_timestamp');
        $this->forge->dropColumn('comments', 'is_deleted');
        $sql = "DROP TRIGGER IF EXISTS add_deleted_timestamp";
        $this->db->query($sql);
    }
}
