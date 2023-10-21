<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class UserModel extends Model {


    protected $primaryKey = 'id';
    public $builder;
    public $db;


    public function __construct() {

        $this->db               = \Config\Database::connect();
        $this->builder          = $this->db->table('users');
    }


    public function checkExist($email = "") {

        if ($email != "") {
            $query = $this->builder->getWhere(['email' => $email]);
        }
        //$this->db->where('deleted !=', 1);

        if ($query->getNumRows()) {
            $result = $query->getRowArray();
            return $result['id'];
        } else {
            return false;
        }
    }

    public function checkUserNameExist($username = "") {

        if ($username != "") {
            $query = $this->builder->getWhere(['username' => $username]);
        }
        //$this->db->where('deleted !=', 1);

        if ($query->getNumRows()) {
            $result = $query->getRowArray();
            return $result['id'];
        } else {
            return false;
        }
    }


    function signup($data) {

        if ($this->checkExist($data['email']) !== false) {
            return -1;
        }

        if ($this->checkUserNameExist($data['username']) !== false) {
            return -2;
        }

        if ($this->builder->insert($data)) {
            return $this->db->insertID();
        } else {
            return 0;
        }
    }


    function login($data){
        try {
            $userData = $this->getUser($data['username'], "username");

            if (isset($userData)) {
                $validPassword = password_verify($data['password'], $userData['password']);
                if($validPassword) {
                    return $userData;
                }
                else
                    return false;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }


    function getUser($val, $field = "email") {
        try {
            $userData = $this->builder
                ->where($field, $val)
                ->get()
                ->getRowArray();

            return $userData;
        } catch (Exception $e) {
            return 0;
        }
    }


    function editUser($id, $data) {

        $this->builder->set($data)
                    ->where('id', $id)
                    ->update();
    }


    function getUserById($id) {

        return $this->builder
                ->where('id', $id)
                ->get()
                ->getRowArray();
    }

    function deleteUserPermanently($id) {

        return $this->builder
                ->where('id', $id)
                ->delete();
    }


    function follow($data, $isFollow) {

        $builder = $this->db->table('follow');
        $query = $builder->where($data)
                        ->get();
        if ($query->getNumRows() > 0) {
            if ($isFollow == 0) {
                $builder->delete($data);
                $this->updateFollowCount($data['target_id'], $isFollow);
            }
        } else {
            $builder->insert($data);
            $this->updateFollowCount($data['target_id'], $isFollow);
        }
    }


    function updateFollowCount($userId, $isFollow) {

        $this->builder->where('id', $userId);
        if ($isFollow == 1) {
            $this->builder->set('followers', 'followers + 1', FALSE);
        } else {
            $this->builder->set('followers', 'followers - 1', FALSE);
        }
        $this->builder->update();
    }


    function checkMyFollow($myId, $userId) {
        $builder = $this->db->table('follow');
        $builder->where('user_id', $myId);
        $builder->where('target_id', $userId);
        return $builder->get()->getNumRows();
    }


    function addNotification($data) {

        $builder = $this->db->table('notifications');
        $builder->insert($data);
    }

    function notifications($userId) {

        $builder = $this->db->table('notifications');
        return $builder->where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
    }

    function contact($data) {

        $builder = $this->db->table('contacts');
        $builder->where('user_id', $data['user_id']);
        $builder->where('target_id', $data['target_id']);
        $query = $builder->get();
        if ($query->getNumRows() > 0) {
            $builder->where('user_id', $data['user_id']);
            $builder->where('target_id', $data['target_id']);
            $builder->set($data);
            $builder->update();
        } else {
            $builder = $this->db->table('contacts');
            $builder->where('target_id', $data['user_id']);
            $builder->where('user_id', $data['target_id']);
            $query = $builder->get();
            if ($query->getNumRows() > 0) {
                $builder->where('target_id', $data['user_id']);
                $builder->where('user_id', $data['target_id']);
                $builder->set($data);
                $builder->update();
            } else {
                $builder->insert($data);
            }
        }
    }

    function getContact($id) {
        $builder = $this->db->table('contacts');
        return $builder->where('user_id', $id)
                ->orWhere('target_id', $id)
                ->get()
                ->getResultArray();
    }

    function searchUsers($keyword, $userId/*, $pageIndex, $count*/) {

        $postData = $this->builder
                ->where('id !=', $userId)
                ->like('username', $keyword)
                // ->orLike('users.name', $keyword)
                // ->get($count, $pageIndex * $count)
                ->get()
                ->getResultArray();

        return $postData;
    }

    function getFollowers($userId) {

        $builder = $this->db->table('follow');
        return $builder->where('user_id', $userId)
                ->get()
                ->getResultArray();
    }
}
?>
