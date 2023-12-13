<?php
namespace App\Controllers\Api;

use App\Controllers\Api\Base;
use App\Models\Api\BlockedUsersModel;
use App\Models\Api\UserModel;
use App\Models\Api\PostModel;
use App\Models\Api\DeviceModel;


class Users extends Base
{
    protected $_userModel;
    protected $_postModel;

    protected $_deviceModel;

    protected $_blockedUsersModel;


    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->_postModel = new PostModel();
        $this->_deviceModel = new DeviceModel();
        $this->_blockedUsersModel = new BlockedUsersModel();

    }

    // edit user information
    function editDisplayName()
    {

        $result = array();
        $userId = $this->request->user->userId;

        $data = array(
            'name' => $this->request->getPost('name'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->_userModel->editUser($userId, $data);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }

    function editMeasurement()
    {

        $result = array();
        $userId = $this->request->user->userId;

        $data = array(
            'height' => $this->request->getPost('height'),
            'weight' => $this->request->getPost('weight'),
            'bust' => $this->request->getPost('bust'),
            'waist' => $this->request->getPost('waist'),
            'hips' => $this->request->getPost('hips'),
            'measurement_privacy' => $this->request->getPost('measurement_privacy'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->_userModel->editUser($userId, $data);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }

    function editStyles()
    {

        $result = array();
        $userId = $this->request->user->userId;
        $data = ['styles' => $this->request->getPost('styles'),];

        $this->_userModel->editUser($userId, $data);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }

    function uploadFile()
    {

        $userId = $this->request->user->userId;

        $validationRule = [
            'file' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[file]',
                    'is_image[file]',
                    'mime_in[file,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[file,4096]',
                    'max_dims[file,1024,1024]',
                ],
            ],
        ];

        $response = [
            'status' => false,
            'data' => $validationRule,
            'message' => "Image could not upload"
        ];

        if ($validationRule) {
            $imageFile = $this->request->getFile('file');
            $newName = $imageFile->getRandomName();
            $imageFile->move('../public/uploads', $newName);

            // $imageFile->move(WRITEPATH . 'uploads', $newName);
            $data = ['photo_name' => $newName];
            // $data = [
            // 'photo_name' => $imageFile->getClientName(),
            // 'file'  => $imageFile->getClientMimeType()
            // ];

            // $this->_userModel->editUser($userId, $data);

            $filepath = base_url() . "uploads/" . $newName;
            $response = [
                'status' => true,
                'data' => $filepath,
                'message' => "Image successfully uploaded"
            ];
        }

        return $this->response->setJSON($response);

    }

    function editProfile()
    {

        $userId = $this->request->user->userId;

        $deletePhoto = $this->request->getPost('delete_photo');

        $data = array(
            'name' => $this->request->getPost('name'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $filepath = '';

        if ($this->request->getFile('file')) {

            $validationRule = [
                'file' => [
                    'label' => 'Image File',
                    'rules' => [
                        'uploaded[file]',
                        'is_image[file]',
                        'mime_in[file,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                        'max_size[file,4096]',
                        'max_dims[file,1024,1024]',
                    ],
                ],
            ];

            $response = [
                'status' => false,
                'data' => $validationRule,
                'message' => "Image could not upload"
            ];

            if ($validationRule) {
                $imageFile = $this->request->getFile('file');
                $newName = $imageFile->getRandomName();
                $imageFile->move('../public/uploads', $newName);

                // $imageFile->move(WRITEPATH . 'uploads', $newName);
                $data['photo_name'] = $newName;
                $filepath = base_url() . "uploads/" . $newName;
                // $data = [
                // 'photo_name' => $imageFile->getClientName(),
                // 'file'  => $imageFile->getClientMimeType()
                // ];
            }
        }

        if ($deletePhoto == 1) {
            $data['photo_name'] = '';
        }
        $this->_userModel->editUser($userId, $data);


        $response = [
            'status' => true,
            'data' => $filepath,
            'message' => "Profile has been updated successfully."
        ];

        return $this->response->setJSON($response);

    }

    function follow()
    {

        $userId = $this->request->user->userId;

        $targetId = $this->request->getPost('target_id');
        $isFollow = $this->request->getPost('is_follow');

        if ($userId == $targetId) {
            $response = [
                'status' => false,
                'data' => '',
                'message' => "You can't follow yourself."
            ];

            return $this->response->setJSON($response);
            exit;
        }

        $data = [
            'user_id' => $userId,
            'target_id' => $targetId
        ];

        $this->_userModel->follow($data, $isFollow);

        $msg = $isFollow == 0 ? 'unfollowed' : 'followed';
        $message = 'You ' . $msg . ' this user successfully';

        if ($isFollow == 1) {
            // Send Notification
            $user = $this->_userModel->getUserById($userId);
            $targetUser = $this->_userModel->getUserById($targetId);
            $msg = $user['username'] . ' started following you';
            $token = $this->_deviceModel->getPushId($targetId);
            $this->sendNotification($token, array(), $msg);

            // Add notification history
            $notificationData = [
                'user_id' => $targetId,
                'post_id' => 0,
                'sent_by' => $userId,
                'notification_type' => 'follow',
                'content' => $msg,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->_userModel->addNotification($notificationData);
        }

        $response = [
            'status' => true,
            'data' => '',
            'message' => $message
        ];

        return $this->response->setJSON($response);
    }

    function profile($userId)
    {

        $myId = $this->request->user->userId;

        $user = $this->_userModel->getUserById($userId);

        $res['id'] = $user['id'];
        $res['username'] = $user['username'];
        $res['name'] = $user['name'];
        $res['styles'] = $user['styles'];
        $res['weight'] = $user['weight'];
        $res['height'] = $user['height'];
        $res['bust'] = $user['bust'];
        $res['waist'] = $user['waist'];
        $res['hips'] = $user['hips'];
        $res['followers'] = $user['followers'];
        $res['device_type'] = $user['device_type'];
        $res['photo_url'] = base_url() . 'uploads/' . $user['photo_name'];
        if ($myId == -1) {
            $myFollow = 0;
        } else {
            $myFollow = $this->_userModel->checkMyFollow($myId, $userId);
        }

        $res['my_follow'] = $myFollow;

        $userPosts = $this->_postModel->getByUser($res['id']);

        $posts = array();

        foreach ($userPosts as $row) {
            if ($myId == -1) {
                $myLike = 0;
            } else {
                $myLike = $this->_postModel->checkMyLike($myId, $row['id']);
            }
            $post = [
                'id' => $row['id'],
                'image' => base_url() . 'uploads/' . $row['image'],
                'caption' => $row['caption'],
                'chat_enabled' => $row['chat_enabled'],
                'hypertext' => $row['hypertext'],
                'hyperlink' => $row['hyperlink'],
                'added_by' => $row['added_by'],
                'created_at' => $row['created_at'],
                'likes' => $row['likes'],
                'my_like' => $myLike
            ];
            array_push($posts, $post);
        }

        $res['posts'] = $posts;

        $response = [
            'status' => true,
            'data' => $res,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }

    function notifications()
    {

        $result = array();

        $userId = $this->request->user->userId;

        $notifications = $this->_userModel->notifications($userId);

        foreach ($notifications as $row) {
            $type = $row['notification_type'];
            // $post = new \stdClass();
            $post = null;
            if (($type == 'like' || $type == 'comment') && $row['post_id'] != 0) {
                $postData = $this->_postModel->getById($row['post_id']);
                if (!empty($postData)) {
                    $myLike = $this->_postModel->checkMyLike($userId, $row['post_id']);
                    $post = [
                        'id' => $postData['id'],
                        'image' => base_url() . 'uploads/' . $postData['image'],
                        'caption' => $postData['caption'],
                        'chat_enabled' => $postData['chat_enabled'],
                        'hypertext' => $postData['hypertext'],
                        'hyperlink' => $postData['hyperlink'],
                        'added_by' => $postData['added_by'],
                        'created_at' => $postData['created_at'],
                        'likes' => $postData['likes'],
                        'my_like' => $myLike,
                    ];
                } else {
                    continue;
                }
            }
            $user = $this->_userModel->getUserById($row['sent_by']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];

            $row['userData'] = $user;
            $row['postData'] = $post;

            array_push($result, $row);
        }

        $response = [
            'status' => true,
            'data' => $result,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }

    function sendMessage()
    {

        $userId = $this->request->user->userId;
        $targetId = $this->request->getPost('target_id');
        $message = $this->request->getPost('message');
        $message_type = $this->request->getPost('message_type');

        $data = [
            'user_id' => $userId,
            'target_id' => $targetId,
            'last_message' => $message,
            'last_timestamp' => time(),
            'last_date' => date('Y-m-d H:i:s')
        ];

        $this->_userModel->contact($data);


        // Send Notification
        $user = $this->_userModel->getUserById($userId);
        $targetUser = $this->_userModel->getUserById($targetId);
        $payload = [
            'user_id' => $userId,
            'target_id' => $targetId,
            'message_type' => $message_type,
            'message' => $message,
        ];
        $token = $this->_deviceModel->getPushId($targetId);


        $title = strlen($user['name']) > 0 ? $user['name'] : $user['username'];
        if ($token) {
            $this->sendNotification($token, $payload, $message, $title, "Has sent you a message");
        }


        $response = [
            'status' => true,
            'data' => $data,
            'message' => "You sent message successfully"
        ];

        return $this->response->setJSON($response);
    }

    function contacts()
    {

        $result = array();

        $userId = $this->request->user->userId;
        $contacts = $this->_userModel->getContact($userId);

        foreach ($contacts as $row) {

            if ($row['user_id'] == $userId) {
                $targetId = $row['target_id'];
            } else {
                $targetId = $row['user_id'];
            }
            $user = $this->_userModel->getUserById($targetId);

            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];

            $contact = [
                'id' => $row['id'],
                'target_id' => $targetId,
                'last_message' => $row['last_message'],
                'last_timestamp' => $row['last_timestamp'],
                'last_date' => $row['last_date'],
                'contactUser' => $user
            ];

            array_push($result, $contact);
        }

        $response = [
            'status' => true,
            'data' => $result,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }

    function changePassword()
    {

        $result = array();
        $userId = $this->request->getPost('user_id');

        $password_hash = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $data = array('password' => $password_hash);

        $this->_userModel->editUser($userId, $data);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }

    function blockUser()
    {
        $userId = $this->request->user->userId;
        $targetId = $this->request->getPost('target_id');
        $fields = [
            "blocked_by" => $userId,
            "user_id" => $targetId
        ];
        $this->_blockedUsersModel->add($fields);
        $result = ['status' => true, 'data' => ""];
        return $this->respond($result, 200);
    }
}