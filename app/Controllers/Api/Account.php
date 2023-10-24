<?php
namespace App\Controllers\Api;

use App\Controllers\Api\Base;
use App\Models\Api\DeviceModel;
use App\Models\Api\PostModel;
use App\Models\Api\UserModel;

class Account extends Base
{
    protected $_deviceModel;
    protected $_postModel;

    protected $_userModel;

    public function __construct()
    {
        $this->_deviceModel = new DeviceModel();
        $this->_postModel = new PostModel();
        $this->_userModel = new UserModel();
    }

    // edit user information
    function registerDeviceToken()
    {
        $userId = $this->request->user->userId;
        $token = $this->request->getPost('push_token');
        $this->_deviceModel->upsertDevicePushToken($userId, $token);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }

    function permDelete()
    {
        $userId = $this->request->user->userId;

        $imagesToDelete = $this->_postModel->builder->where("added_by = $userId")->select("image")->get()->getResultArray();

        foreach ($imagesToDelete as $imageObject) {
            $fileName = $imageObject['image'];
            $filePath = "../public/uploads/$fileName";

            if (is_file($filePath)) {
                if (unlink($filePath)) {
                    echo "File successfully deleted!";
                } else {
                    echo "File could not be deleted.";
                }
            } else {
                echo "File does not exist.";
            }

        }

        $this->_userModel->delete($userId);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);

    }
}