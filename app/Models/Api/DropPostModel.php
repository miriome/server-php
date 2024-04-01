<?php

namespace App\Models\Api;

use App\Helpers\Helpers;
use CodeIgniter\Model;
use Exception;

class DropPostModel extends Model
{

    protected $table = 'drop_posts';
    protected $primaryKey = 'id';
    public $builder;

    private $dropPostImagesBuilder;
    private $dropBrandSizingBuilder;
    private $dropBodySizingBuilder;
    public $db;

    protected $_userModel;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
        $this->dropPostImagesBuilder = $this->db->table("drop_post_images");
        $this->dropBodySizingBuilder = $this->db->table("drops_body_sizing");
        $this->dropBrandSizingBuilder = $this->db->table("drops_brand_sizing");
    }

    public function getAllPosts($dropId)
    {

        $postData = $this->builder
            ->select()
            ->where('dropId', $dropId)
            ->join("drop_post_images", "drop_post_images.dropPostId = drop_posts.id AND index = 0", 'left')
            ->get()
            ->getResultArray();

        return $postData;

    }

    public function getBodySizing($dropPostId)
    {
        $bodySizing = $this->dropBodySizingBuilder->select()->where('dropPostId', $dropPostId)->get()->getRowArray();
        return $bodySizing;
    }

    public function getBrandSizing($dropPostId)
    {
        $bodySizing = $this->dropBrandSizingBuilder->select()->where('dropPostId', $dropPostId)->get()->getResultArray();
        return $bodySizing;
    }

    public function getPostDetails($dropPostId)
    {

        $postData = $this->builder
            ->select()
            ->where('id', $dropPostId)
            ->get()
            ->getRowArray();

        return $postData;

    }

    public function getImagesForDrop($dropPostId)
    {

        $images = $this->dropPostImagesBuilder
            ->select()
            ->where('dropPostId', $dropPostId)
            ->get()
            ->getResultArray();
        return $images;

    }

}
