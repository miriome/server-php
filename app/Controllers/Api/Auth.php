<?php
namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Firebase\JWT\JWT;
use App\Models\Api\UserModel;

class Auth extends ResourceController
{
    protected $_userModel;

    public function __construct()
    {

        $this->_userModel = new UserModel();
    }

    public function login(){

        $res = array();
        $data = array(
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password')
        );

        $result = $this->_userModel->login($data);

        if ($result === false) {
            $res = [
                'status' => false,
                'data' => "",
                'message' => 'Invalid Username or Password!'
            ];

            return $this->respond($res, 200);

        } else {

            $key = Services::getSecretKey();
			$payload = [
				'userId' => $result['id'],
				'username' => $result['username'],
				'email' => $result['email'],
			];

			$jwt = JWT::encode($payload, $key, 'HS256');


            $res['id'] = $result['id'];
            $res['pronouns'] = $result['pronouns'];
            $res['username'] = $result['username'];
            $res['name'] = $result['name'];
            $res['email'] = $result['email'];
            $res['styles'] = $result['styles'];
            $res['weight'] = $result['weight'];
            $res['height'] = $result['height'];
            $res['bust'] = $result['bust'];
            $res['waist'] = $result['waist'];
            $res['hips'] = $result['hips'];
            $res['followers'] = $result['followers'];
            $res['device_type'] = $result['device_type'];
            $res['photo_url'] = base_url() . 'uploads/' . $result['photo_name'];


            $rresult['status'] = TRUE;
            $rresult['data'] = $res;
            $rresult['access_token'] = $jwt;

            return $this->respond($rresult, 200);
        }
    }

    public function signup(){

        $result = array();

        $password_hash = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $data = array(
                'pronouns'          => $this->request->getPost('pronouns'),
                'styles'            => $this->request->getPost('styles'),
                'username'          => $this->request->getPost('username'),
                'email'             => $this->request->getPost('email'),
                'password'          => $password_hash,
                'device_type'       => $this->request->getPost('device_type'),
                'created_at'        => date('Y-m-d H:i:s')
            );


        $insert_id = $this->_userModel->signup($data);

        if ($insert_id > 0) {

            $userdata = array(
                'userId'       => $insert_id,
                'username'      => $data['username'],
                'email'         => $data['email']
            );

            $result['data'] = $userdata;
            $key = Services::getSecretKey();

            $jwt = JWT::encode($userdata, $key, 'HS256');
            $result['access_token'] = $jwt;
            $result['status'] = true;

            return $this->respond($result, 200);
        }
        else if ($insert_id == 0) {
            $error = "Something went wrong!";
        } else if ($insert_id == -1) {
            $error = "Username is already registered";
        } else if ($insert_id == -2) {
            $error = "Email is already registerd!";
        }
        $res = ['status' => false,
                'data' => $insert_id,
                'message' => $error];
        return $this->respond($res, 200);
    }

    public function checkDuplicate() {

        $fieldName = $this->request->getPost('field_name');
        $fieldValue = $this->request->getPost('field_value');

        if ($fieldName == 'username') {
            if (strpos($fieldValue,'miromie') !== false) {
                $result = true;
            } else {
                $result = $this->_userModel->checkUserNameExist($fieldValue);
            }
            
        } else if ($fieldName == 'email') {
            $result = $this->_userModel->checkExist($fieldValue);
        }

        $response = [
            'status' => true,
            'data' => ['exist' => $result == false ? false : true],
            'message' => $result != false ? $fieldName .' exist' : $fieldName . ' not exist'
        ];

        return $this->respond($response, 200);
    }


    public function guestToken() {

        $key = Services::getSecretKey();
        $payload = [
            'userId' => -1,
            'username' => 'guest',
            'email' => '',
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        $payload['access_token'] = $jwt;

        $result['status'] = TRUE;
        $result['data'] = $payload;
        $result['message'] = "";

        return $this->respond($result, 200);

    }
}
