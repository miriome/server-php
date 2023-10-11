<?php namespace App\Controllers\Api;

use App\Controllers\Api\Base;
use App\Models\Api\DeviceModel;


class Account extends Base
{
    protected $_deviceModel;

    public function __construct()
    {
        $this->_deviceModel = new DeviceModel();
    }

    // edit user information
    function registerDevicePushToken() {
        error_log('ran');
        $userId = $this->request->user->userId;
        error_log($userId);
        $token = $this->request->getPost('push_token');
        $this->_deviceModel->upsertDevicePushToken($userId, $token);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }
}