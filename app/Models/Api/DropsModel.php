<?php

namespace App\Models\Api;

use CodeIgniter\Model;
use Exception;

class DropsModel extends Model
{

    protected $table = 'drops';
    protected $primaryKey = 'id';
    public $builder;

    public $db;

    protected $_userModel;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
    }

    public function getDrop()
    {

        $dropData = $this->builder
            ->select()
            ->orderBy('startTimestamp', 'desc')
            ->limit(1)
            ->get()->getRowArray();

        return $dropData;

    }

}
