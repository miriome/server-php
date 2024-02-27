<?php

namespace App\Models\Api;
use CodeIgniter\Model;


class BrandSizingModel extends Model
{

    protected $table = 'brand_sizing';
    protected $primaryKey = 'id';
    public $builder;

    private $postImagesBuilder;
    public $db;

    protected $_userModel;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
    }

    function getAllBrandSizings(): array {
        return $this->builder->get()->getResultArray();
    }
}
