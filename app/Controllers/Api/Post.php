<?php

namespace App\Controllers\Api;

use App\Controllers\Api\Base;
use App\Helpers\Helpers;
use App\Models\Api\PostModel;
use App\Models\Api\UserModel;
use App\Models\Api\DeviceModel;

class Post extends Base
{
    protected $_postModel;
    protected $_userModel;
    protected $_deviceModel;
    public function __construct()
    {
        $this->_postModel = new PostModel();
        $this->_userModel = new UserModel();
        $this->_deviceModel = new DeviceModel();
    }

    public function index()
    {
        //
    }

    public function addPost()
    {

        $userId = $this->request->user->userId;
        $data = array(
            'caption' => $this->request->getPost('caption'),
            'chat_enabled' => $this->request->getPost('chat_enabled'),
            'hashtag' => $this->request->getPost('hashtag'),
            'hypertext' => $this->request->getPost('hypertext'),
            'hyperlink' => $this->request->getPost('hyperlink'),
            'created_at' => time(),
            'added_by' => $userId,
        );

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

        function debugArray(array $data, $filename = "Array")
        {
            $f = fopen("bebug_" . $filename . ".txt", "w");
            fwrite($f, print_r($data, true));
            fclose($f);
        }

        if ($validationRule) {
            $imageFile = $this->request->getFile('file');
            debugArray(["file" => $imageFile], "array");

            $newName = $imageFile->getRandomName();
            $imageFile->move('../public/uploads', $newName);

            $data['image'] = $newName;


            $result = $this->_postModel->add($data);
            $postId = $result[0];
            $mentionedUsers = $result[1];
            $user = $this->_userModel->getUserById($userId);

            // Send mention notification
            foreach ($mentionedUsers as $mentionedUser) {
                $msg = "You were mentioned in {$user['username']}'s comment";
                $token = $this->_deviceModel->getPushId($mentionedUser['id']);
                $this->sendNotification($token, array(), $msg);

                // Add notification history
                $notificationData = [
                    'user_id' => $mentionedUser['id'],
                    'post_id' => $postId,
                    'sent_by' => $userId,
                    'notification_type' => 'mention',
                    'content' => $msg,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->_userModel->addNotification($notificationData);

            }

            $filepath = base_url() . "uploads/" . $newName;
            $response = [
                'status' => true,
                'data' => $filepath,
                'message' => "Post is added successfully"
            ];
        }

        return $this->response->setJSON($response);
    }


    function editPost($postId)
    {

        $userId = $this->request->user->userId;

        $data = array(
            'caption' => $this->request->getPost('caption'),
            'chat_enabled' => $this->request->getPost('chat_enabled'),
            'hashtag' => $this->request->getPost('hashtag'),
            'hypertext' => $this->request->getPost('hypertext'),
            'hyperlink' => $this->request->getPost('hyperlink'),
            'updated_at' => time()
        );

        $msg = "Post is updated successfully";
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

            if ($validationRule) {
                $imageFile = $this->request->getFile('file');
                $newName = $imageFile->getRandomName();
                $imageFile->move('../public/uploads', $newName);

                // $imageFile->move(WRITEPATH . 'uploads', $newName);
                $data['image'] = $newName;
                // $data = [
                // 'photo_name' => $imageFile->getClientName(),
                // 'file'  => $imageFile->getClientMimeType()
                // ];
                $filepath = base_url() . "uploads/" . $newName;
            } else {
                $msg = "Image could not upload";
            }

        }

        $this->_postModel->editPost($postId, $data);



        $response = [
            'status' => true,
            'data' => $filepath,
            'message' => $msg
        ];

        return $this->response->setJSON($response);
    }

    function markSold($postId)
    {

        $userId = $this->request->user->userId;

        $data = array(
            'chat_enabled' => $this->request->getPost('chat_enabled'),
            'updated_at' => time()
        );

        $msg = "Post is marked as sold successfully";
        $filepath = '';

        $this->_postModel->editPost($postId, $data);

        $response = [
            'status' => true,
            'data' => '',
            'message' => $msg
        ];

        return $this->response->setJSON($response);
    }


    function deletePost($postId)
    {

        $data = ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')];
        $this->_postModel->editPost($postId, $data);

        $response = [
            'status' => true,
            'data' => '',
            'message' => "Post is deleted successfully"
        ];

        return $this->response->setJSON($response);
    }


    function getPost()
    {

        $result = array();
        $followers = array();

        $userId = $this->request->user->userId;

        $pageIndex = $this->request->getPost('page_index');
        $count = $this->request->getPost('count');


        if ($userId == -1) {
            $posts = $this->_postModel->getAllPosts($pageIndex, $count);

        } else {

            $followers = $this->_userModel->getFollowers($userId);
            $fcount = count($followers);
            $posts = $this->_postModel->getPosts($pageIndex, $count, $userId, $fcount);

            if (count($posts) == 0) {

                $user = $this->_userModel->getUserById($userId);
                $userStyles = [];
                if ($user['styles'] != '') {
                    $userStyles = explode(',', $user['styles']);
                }
                $posts = $this->_postModel->getPostsByStyles($pageIndex, $count, $userStyles, $fcount);
            }
        }

        foreach ($posts as $row) {
            $user = $this->_userModel->getUserById($row['added_by']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'styles' => $user['styles'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];
            if ($userId == -1) {
                $myLike = 0;
            } else {
                $myLike = $this->_postModel->checkMyLike($userId, $row['id']);
            }
            $row = [
                'id' => $row['id'],
                'image' => base_url() . 'uploads/' . $row['image'],
                'caption' => $row['caption'],
                'chat_enabled' => $row['chat_enabled'],
                'hashtag' => $row['hashtag'],
                'hypertext' => $row['hypertext'],
                'hyperlink' => $row['hyperlink'],
                'added_by' => $row['added_by'],
                'created_at' => $row['created_at'],
                'likes' => $row['likes'],
                'deleted' => $row['deleted'],
                'my_like' => $myLike,
                'posted_by' => $user
            ];

            array_push($result, $row);
        }

        $response = [
            'status' => true,
            'data' => $result,
            'followers' => count($followers),
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }


    function getDetail($postId)
    {

        $result = array();

        $userId = $this->request->user->userId;

        $post = $this->_postModel->getById($postId);

        if (!isset($post)) {

            $response = [
                'status' => false,
                'data' => [],
                'message' => "Post does not exist"
            ];
            return $this->response->setJSON($response);
        }

        $puser = $this->_userModel->getUserById($post['added_by']);

        $poster = [
            'id' => $puser['id'],
            'username' => $puser['username'],
            'name' => $puser['name'],
            'email' => $puser['email'],
            'followers' => $puser['followers'],
            'height' => $puser['height'],
            'weight' => $puser['weight'],
            'bust' => $puser['bust'],
            'waist' => $puser['waist'],
            'hips' => $puser['hips'],
            'photo_url' => base_url() . 'uploads/' . $puser['photo_name'],
        ];
        if ($userId == -1) {
            $myLike = 0;
        } else {
            $myLike = $this->_postModel->checkMyLike($userId, $post['id']);
        }

        $comments = array();
        $commentData = $this->_postModel->comments($postId);

        foreach ($commentData as $row) {

            $user = $this->_userModel->getUserById($row['user_id']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'height' => $user['height'],
                'weight' => $user['weight'],
                'bust' => $user['bust'],
                'waist' => $user['waist'],
                'hips' => $user['hips'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];
            $row['commented_by'] = $user;

            array_push($comments, $row);
        }

        $post = [
            'id' => $post['id'],
            'image' => base_url() . 'uploads/' . $post['image'],
            'caption' => $post['caption'],
            'chat_enabled' => $post['chat_enabled'],
            'hashtag' => $post['hashtag'],
            'hypertext' => $post['hypertext'],
            'hyperlink' => $post['hyperlink'],
            'added_by' => $post['added_by'],
            'mentions' => $post['mentions'],
            'likes' => $post['likes'],
            'created_at' => $post['created_at'],
            'my_like' => $myLike,
            'posted_by' => $poster,
            'comments' => $comments
        ];

        $response = [
            'status' => true,
            'data' => $post,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }


    function setLike()
    {

        $userId = $this->request->user->userId;
        $postId = $this->request->getPost('post_id');
        $isLike = $this->request->getPost('is_like');

        $data = ['user_id' => $userId,
            'post_id' => $postId
        ];

        $this->_postModel->setLike($data, $isLike);

        $likeMessage = $isLike == 0 ? 'disliked' : 'liked';
        $message = 'You ' . $likeMessage . ' this post successfully';

        if ($isLike == 1) {
            // Send Notification
            $post = $this->_postModel->getById($postId);
            if ($userId != $post['added_by']) {
                $user = $this->_userModel->getUserById($userId);

                $msg = $user['username'] . ' ' . $likeMessage . ' your post';
                $token = $this->_deviceModel->getPushId($post['added_by']);
                $this->sendNotification($token, array(), $msg);

                if ($post['added_by'] != $userId) {
                    // Add notification history
                    $notificationData = [
                        'user_id' => $post['added_by'],
                        'post_id' => $postId,
                        'sent_by' => $userId,
                        'notification_type' => 'like',
                        'content' => $msg,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->_userModel->addNotification($notificationData);
                }
            }
        }

        $response = [
            'status' => true,
            'data' => '',
            'message' => $message,
        ];

        return $this->response->setJSON($response);
    }

    // Add comment
    function comment()
    {

        $userId = $this->request->user->userId;

        $postId = $this->request->getPost('post_id');

        $data = ['user_id' => $userId,
            'post_id' => $postId,
            'comment' => $this->request->getPost('comment'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_timestamp' => time()];

        $mentionedUsers = $this->_postModel->addComment($data);

        $post = $this->_postModel->getById($postId);
        $user = $this->_userModel->getUserById($userId);
        // Send mention notification
        foreach ($mentionedUsers as $mentionedUser) {
            $msg = "You were mentioned in {$user['username']}'s comment";
            $token = $this->_deviceModel->getPushId($mentionedUser['id']);
            $this->sendNotification($token, array(), $msg);

            // Add notification history
            $notificationData = [
                'user_id' => $mentionedUser['id'],
                'post_id' => $postId,
                'sent_by' => $userId,
                'notification_type' => 'mention',
                'content' => $msg,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->_userModel->addNotification($notificationData);

        }
        // Send Comment Notification
        if ($userId != $post['added_by']) {
            $msg = $user['username'] . ' commented your post';
            $token = $this->_deviceModel->getPushId($post['added_by']);
            $this->sendNotification($token, array(), $msg);
            // Add notification history
            $notificationData = [
                'user_id' => $post['added_by'],
                'post_id' => $postId,
                'sent_by' => $userId,
                'notification_type' => 'comment',
                'content' => $msg,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->_userModel->addNotification($notificationData);

        }

        $response = [
            'status' => true,
            'data' => '',
            'message' => "You've added comment successfully",
        ];

        return $this->response->setJSON($response);

    }

    // Get comments
    function comments($postId)
    {

        $result = array();

        $comments = $this->_postModel->comments($postId);

        foreach ($comments as $row) {

            $user = $this->_userModel->getUserById($row['user_id']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];

            $row['commented_by'] = $user;
            $one['commentData'] = $row;

            array_push($result, $one);
        }

        $response = [
            'status' => true,
            'data' => $result,
            'message' => "",
        ];

        return $this->response->setJSON($response);
    }

    // delete comment
    function deleteComment()
    {

        $commentId = $this->request->getPost('comment_id');
        $this->_postModel->deleteComment($commentId);

        $response = [
            'status' => true,
            'data' => '',
            'message' => "Comment deleted.",
        ];

        return $this->response->setJSON($response);

    }


    function search()
    {

        $result = array();
        $postResult = array();
        $userResult = array();

        $userId = $this->request->user->userId;

        $keyword = $this->request->getPost('keyword');
        $pageIndex = $this->request->getPost('page_index');
        $count = $this->request->getPost('count');

        if ($keyword == '') {
            $posts = $this->_postModel->otherPosts($userId);
        } else {
            $posts = $this->_postModel->searchPosts($keyword, $userId /*, $pageIndex, $count*/);
        }
        foreach ($posts as $row) {
            $user = $this->_userModel->getUserById($row['added_by']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'styles' => $user['styles'],
                'followers' => $user['followers'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];
            if ($userId == -1) {
                $myLike = 0;
            } else {
                $myLike = $this->_postModel->checkMyLike($userId, $row['id']);
            }

            $post = [
                'id' => $row['id'],
                'image' => base_url() . 'uploads/' . $row['image'],
                'caption' => $row['caption'],
                'chat_enabled' => $row['chat_enabled'],
                'hashtag' => $row['hashtag'],
                'hypertext' => $row['hypertext'],
                'hyperlink' => $row['hyperlink'],
                'added_by' => $row['added_by'],
                'likes' => $row['likes'],
                'created_at' => $row['created_at'],
                'my_like' => $myLike,
                'posted_by' => $user
            ];

            array_push($postResult, $post);
        }

        if ($keyword != '') {
            $users = $this->_userModel->searchUsers($keyword, $userId /*, $pageIndex, $count*/);

            foreach ($users as $row) {

                $user = [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'followers' => $row['followers'],
                    'styles' => $row['styles'],
                    'photo_url' => base_url() . 'uploads/' . $row['photo_name']
                ];

                array_push($userResult, $user);
            }
        }

        $result = [
            'postData' => $postResult,
            'userData' => $userResult
        ];

        $response = [
            'status' => true,
            'data' => $result,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }

    // Get liked post list
    function liked()
    {

        $result = array();

        $userId = $this->request->user->userId;

        $pageIndex = $this->request->getPost('page_index');
        $count = $this->request->getPost('count');

        $posts = $this->_postModel->likedPosts($userId, $pageIndex, $count);

        foreach ($posts as $row) {
            $user = $this->_userModel->getUserById($row['added_by']);
            $user = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'followers' => $user['followers'],
                'photo_url' => base_url() . 'uploads/' . $user['photo_name']
            ];
            if ($userId == -1) {
                $myLike = 0;
            } else {
                $myLike = $this->_postModel->checkMyLike($userId, $row['id']);
            }

            $post = [
                'id' => $row['id'],
                'image' => base_url() . 'uploads/' . $row['image'],
                'caption' => $row['caption'],
                'chat_enabled' => $row['chat_enabled'],
                'hashtag' => $row['hashtag'],
                'hypertext' => $row['hypertext'],
                'hyperlink' => $row['hyperlink'],
                'added_by' => $row['added_by'],
                'likes' => $row['likes'],
                'created_at' => $row['created_at'],
                'my_like' => $myLike,
                'posted_by' => $user
            ];

            array_push($result, $post);
        }

        $response = [
            'status' => true,
            'data' => $result,
            'message' => ""
        ];

        return $this->response->setJSON($response);
    }

}
