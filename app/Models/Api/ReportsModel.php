<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class ReportsModel extends Model
{

    protected $table            = 'reports';
    protected $primaryKey       = 'id';
    public $builder;
    public $db;

    public function __construct() {

        $this->db               = \Config\Database::connect();
        $this->builder          = $this->db->table($this->table);
    }

    public function add($data) {

        $this->builder->insert($data);
        return $this->db->insertID();

    }

}
