<?php
namespace App\Controllers\Api\users;

use App\Controllers\Api\Base;
use App\Models\Api\BrandSizingModel;


class Measurements extends Base
{
    protected $_brandSizingModel;


    public function __construct()
    {
        $this->_brandSizingModel = new BrandSizingModel();

    }

    function getBrandSizings()
    {
        $brandSizes = $this->_brandSizingModel->getAllBrandSizings();
        $result = ['status' => true, 'data' => $brandSizes];
        return $this->respond($result, 200);
    }

}