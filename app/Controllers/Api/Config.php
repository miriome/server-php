<?php
namespace App\Controllers\Api;

use App\Controllers\Api\Base;

class Config extends Base
{

    public $db;
    public function __construct()
    {
        $this->db  = \Config\Database::connect();
    }

    // Get upgrade info
    function updateInfo()
    {
        $row = $this->db->table('update_info')->select()->get()->getRow();
        $result = ['status' => true, 'data' => $row];
        return $this->respond($result, 200);
    }


}