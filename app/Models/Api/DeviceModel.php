<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class DeviceModel extends Model
{

    protected $table = 'device';
    protected $primaryKey = 'id';
    public $builder;
    public $db;

    public function __construct()
    {

        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
    }

    public function add($data)
    {

        $this->builder->insert($data);
        return $this->db->insertID();

    }

    public function upsertDevicePushToken($uid, $token, $platform)
    {
        $hasToken = $this->builder->where('uid', $uid)->countAllResults() > 0;
        if ($hasToken) {
            $this->builder->where('uid', $uid)->set('device_push_token', $token)->set("platform",$platform ?? "ios")->update();
        } else {
            $this->add([
                'device_push_token' => $token,
                'uid' => $uid,
                "platform" => $platform ?? "ios"
            ]);
        }

    }

    public function getPushInfo($uid)
    {
        $tokenResult = $this->builder->where('uid', $uid)->get()->getRowArray();
        return $tokenResult;

    }


}
