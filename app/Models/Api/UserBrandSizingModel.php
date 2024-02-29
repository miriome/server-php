<?php

namespace App\Models\Api;

use CodeIgniter\Model;


class UserBrandSizingModel extends Model
{

    protected $table = 'user_brand_sizing';
    protected $primaryKey = 'id';
    public $builder;

    private $postImagesBuilder;
    public $db;

    public function __construct()
    {

        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
    }

    function getUserBrandSizing($userId): array
    {
        $brandSizings = $this->builder->where('user_id', $userId)->get()->getResultArray();
        usort($brandSizings, function ($a, $b) {
            $ordering = ["Tops" => 1, "Bottoms" => 2, "Dresses / Jumpsuits" => 3, "Jeans" => 4];
            return $ordering[$a["clothing_type"]] - $ordering[$b["clothing_type"]];
        });
        return $brandSizings;
    }

    function setUserBrandSizing($userId, $brandSizings)
    {
        $dbEntries = array();
        $this->builder->where('id', $userId)->delete();
        foreach ($brandSizings as $sizings) {
            $data = [
                'brand_name' => $sizings['brand_name'],
                'size' => $sizings['size'],
                'clothing_type' => $sizings['clothing_type'],
                'user_id' => $userId
            ];
            array_push($dbEntries, $data);
        }
        $this->builder->insertBatch($dbEntries);
    }

}
