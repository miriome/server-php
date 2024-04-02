<?php
namespace App\Controllers\Api;

use App\Controllers\Api\Base;
use App\Models\Api\DropPostModel;
use App\Models\Api\DropsModel;

class Drops extends Base
{

    protected $_dropsPostModel;
    protected $_dropsModel;

    public function __construct()
    {

        $this->_dropsPostModel = new DropPostModel();
        $this->_dropsModel = new DropsModel();
    }

    // Get upgrade info
    function list($dropId)
    {
        $dropList = $this->_dropsPostModel->getAllPosts($dropId);

        $dropData = array_map(function ($dropData) use ($dropId) {
            return [
                'id' => $dropData['id'],
                'title' => $dropData['title'],
                'isPetite' => $dropData['isPetite'],
                'price' => $dropData['price'],
                'condition' => $dropData['condition'],
                'clothingSize' => $dropData['clothingSize'],
                'size' => $dropData['clothingSize'],
                'isSold' => $dropData['isSold'],
                'images' => [
                    [
                        'image' => "drop_{$dropId}/{$dropData['image']}",
                        'index' => $dropData['index']
                    ]
                ],
                'caption' => $dropData['caption'],
            ];
        }, $dropList);
        $result = ['status' => true, 'data' => $dropData];
        return $this->respond($result, 200);
    }

    function getDropPostDetails($dropPostId)
    {
        $dropPost = $this->_dropsPostModel->getPostDetails($dropPostId);
        $dropPostImages = $this->_dropsPostModel->getImagesForDrop($dropPostId, $dropPost['dropId']);
        $dropPostNumberSizing = $this->_dropsPostModel->getBodySizing($dropPostId);
        $dropPostBrandSizing = $this->_dropsPostModel->getBrandSizing($dropPostId);
        $data = [
            'id' => $dropPost['id'],
            'title' => $dropPost['title'],
            'isPetite' => $dropPost['isPetite'],
            'price' => $dropPost['price'],
            'condition' => $dropPost['condition'],
            'clothingSize' => $dropPost['clothingSize'],
            'size' => $dropPost['clothingSize'],
            'isSold' => $dropPost['isSold'],
            'images' => $dropPostImages,
            'caption' => $dropPost['caption'],
            'numberSizing' => $dropPostNumberSizing,
            'brandSizing' => $dropPostBrandSizing
        ];
        $result = ['status' => true, 'data' => $data];
        return $this->respond($result, 200);
    }


    function getDrop()
    {
        $drop = $this->_dropsModel->getDrop();
        $result = ['status' => true, 'data' => $drop];
        return $this->respond($result, 200);
    }


}