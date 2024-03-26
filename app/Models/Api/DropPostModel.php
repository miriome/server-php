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
    public $db;

    protected $_userModel;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
        $this->dropPostImagesBuilder = $this->db->table("drop_post_images");
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

}
