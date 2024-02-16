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
        $dismissable = $this->db->table('update_info')->where('force', 0)->select()->get()->getRow();
        $forced = $this->db->table('update_info')->where('force', 1)->select()->get()->getRow();
        $result = ['status' => true, 'data' => [
            'dismissable' => $dismissable,
            'forced' => $forced
        ]];
        return $this->respond($result, 200);
    }


}