<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CommentsPosts extends Migration
{
    public function up()
    {
        //
        $this->db->query('ALTER TABLE comments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->db->query('ALTER TABLE comments CHANGE comment comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $this->db->query('ALTER TABLE posts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->db->query('ALTER TABLE posts CHANGE caption caption TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    }

    public function down()
    {
        //

        $this->db->query('ALTER TABLE comments CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
        $this->db->query('ALTER TABLE comments CHANGE comment comment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->db->query('ALTER TABLE posts CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
        $this->db->query('ALTER TABLE posts CHANGE caption caption TEXT CHARACTER SET utf8 COLLATE utf8_general_ci');
    }
}
