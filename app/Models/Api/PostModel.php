<?php

namespace App\Models\Api;

use App\Helpers\Helpers;
use CodeIgniter\Model;
use Exception;

class PostModel extends Model
{

    protected $table = 'posts';
    protected $primaryKey = 'id';
    public $builder;

    private $postImagesBuilder;
    public $db;

    protected $_userModel;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
        $this->postImagesBuilder = $this->db->table("post_images");
    }

    public function add($data)
    {

        // Updated below to be users that are mentioned.
        $mentionedUsers = array();

        $this->db->transStart();
        $this->builder->insert($data);
        $postId = $this->db->insertID();
        $caption = $data['caption'];
        // Update post mentions
        if (!is_null($caption)) {
            $mentionedUsernames = Helpers::getUsernamesFromMentions($caption);
        }

        try {
            if (count($mentionedUsernames) > 0) {
                $mentionedUsers = $this->_userModel->getUsersByUsername($mentionedUsernames);
                $mentionedUsersMetadata = array();
                foreach ($mentionedUsers as $mentionedUser) {
                    $insertData = array(
                        'user_id' => $mentionedUser['id'],
                        'post_id' => $postId,
                        'username' => $mentionedUser['username']
                    );
                    array_push($mentionedUsersMetadata, $insertData);
                }
                if (count($mentionedUsersMetadata) > 0) {
                    $this->db->table('posts_mentions')->insertBatch($mentionedUsersMetadata);
                }

            }
        } catch (Exception $e) {
            $f = $e;
        }

        $this->db->transComplete();
        return [$postId, $mentionedUsers];
    }

    function deletePost($postId)
    {

        $data = ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')];
        $this->builder->where('id', $postId)
            ->set($data)
            ->update();

    }

    function markSold($postId)
    {

        $data = array(
            'chat_enabled' => 0,
            'updated_at' => time()
        );

        $this->builder->where('id', $postId)
            ->set($data)
            ->update();

    }

    public function editPost($postId, $data)
    {
        $this->builder->where('id', $postId)
            ->set($data)
            ->update();

        // Updated below to be users that are mentioned.
        $mentionedUsers = array();

        $this->db->transStart();
        $caption = $data['caption'];
        // Update post mentions
        $mentionedUsernames = Helpers::getUsernamesFromMentions($caption);
        try {
            if (count($mentionedUsernames) > 0) {
                $mentionedUsers = $this->_userModel->getUsersByUsername($mentionedUsernames);
                $mentionedUsersMetadata = array();
                foreach ($mentionedUsers as $mentionedUser) {
                    $insertData = array(
                        'user_id' => $mentionedUser['id'],
                        'post_id' => $postId,
                        'username' => $mentionedUser['username']
                    );
                    array_push($mentionedUsersMetadata, $insertData);
                }
                if (count($mentionedUsersMetadata) > 0) {
                    $this->db->table('posts_mentions')->upsertBatch($mentionedUsersMetadata);
                }

            }
        } catch (Exception $e) {
            $f = $e;
        }

        $this->db->transComplete();
        return [$postId, $mentionedUsers];

    }

    // Returns file name of the image that is at index 0
    public function upsertImageForPost($postId, $indexedImageArray)
    {

        $data = array();
        foreach ($indexedImageArray as $indexedImage) {
            $index = $indexedImage['index'];
            $imageFileName = $indexedImage['image'];
            array_push($data, [
                'index' => $index,
                'image' => $imageFileName,
                'post_id' => $postId
            ]);

        }
        $this->postImagesBuilder->upsertBatch($data);

    }

    public function getAllPosts($pageIndex, $count)
    {

        $postData = $this->builder
            ->select('posts.* ')
            ->join('users', "users.id = posts.added_by")
            ->where('users.pronouns !=', "He")
            ->where('deleted', 0)
            ->orderBy('likes', 'DESC')
            // ->orderBy('id', 'DESC')
            ->get($count, $pageIndex * $count)
            ->getResultArray();

        return $postData;

    }

    public function getPostsForNewUsers($pageIndex, $count, $userId)
    {
        // Return posts ordered by rank of likes for each user
        $sql = "SELECT posts.*, RANK() OVER (PARTITION BY posts.added_by ORDER BY likes DESC) AS rank
        FROM posts
        INNER JOIN users ON users.id = posts.added_by
        LEFT JOIN blocked_users ON posts.added_by = blocked_users.user_id
        WHERE users.pronouns != 'HE'
        AND blocked_users.user_id IS NULL
        AND deleted = 0
        AND likes > 0
        ORDER BY rank, likes DESC, RAND()";
        $res = $this->db->query($sql)->getResultArray();
        return $res;
    }


    public function getPostsForFollowedUsers($pageIndex, $count, $userId)
    {

        $postData = $this->builder
            ->select('posts.* ')
            ->join('users', "users.id = posts.added_by")
            ->join('blocked_users', "posts.added_by = blocked_users.user_id", 'left')
            ->where('users.pronouns !=', "He")
            ->where("blocked_users.user_id IS NULL")
            ->where("(added_by IN (SELECT `target_id` FROM follow WHERE `user_id` = $userId) OR added_by = $userId)")
            ->where('deleted', 0)
            ->orderBy('id', 'DESC')
            ->get($count, $pageIndex * $count)
            ->getResultArray();

        return $postData;
    }

    public function getPostsByStyles($pageIndex, $count, $styles, $fcount)
    {

        if (count($styles) > 0) {

            // $this->builder->select('posts.*');
            // $this->builder->join('users', 'users.id = posts.added_by');
            // $this->builder->where('posts.deleted', 0);
            // $this->builder->groupStart();
            // foreach ($styles as $style) {
            //     $this->builder->orLike('users.styles', $style);
            // }
            // $this->builder->groupEnd();

            // $this->builder->orderBy('likes11', 'DESC');
            // $postData = $this->builder->get($count, $pageIndex * $count)->getResultArray();
            // return $postData;

            if ($fcount == 0) {
                $query = "SELECT * FROM (SELECT * FROM (SELECT `posts`.* FROM `posts` JOIN `users` ON `users`.`id` = `posts`.`added_by` LEFT JOIN blocked_users ON `posts`.`added_by` = `blocked_users`.`user_id` WHERE `posts`.`deleted` = 0 AND `blocked_users`.`user_id` IS NULL AND `users`.`pronouns` != 'He' AND (";
                foreach ($styles as $key => $style) {
                    if ($key > 0)
                        $query .= " or ";
                    $query .= "users.styles LIKE '%" . $style . "%'";
                }
                $query .= ") ORDER BY likes DESC) a UNION SELECT * FROM (SELECT posts.* FROM posts JOIN users
                on `users`.`id` = `posts`.`added_by` LEFT JOIN blocked_users ON posts.added_by = blocked_users.user_id WHERE blocked_users.user_id IS NULL AND deleted = 0 AND users.pronouns != 'He' ORDER BY likes DESC) b) c LIMIT " . ($pageIndex * $count) . ", " . $count;

            } else {
                $query = "SELECT * FROM (SELECT * FROM (SELECT `posts`.*, `blocked_users.id` as blockedid FROM `posts` JOIN `users` ON `users`.`id` = `posts`.`added_by` LEFT JOIN blocked_users ON `posts`.`added_by` = `blocked_users`.`user_id` WHERE `posts`.`deleted` = 0 AND `blocked_users`.`user_id` IS NULL AND `users`.`pronouns` != 'He' AND (";
                foreach ($styles as $key => $style) {
                    if ($key > 0)
                        $query .= " or ";
                    $query .= "users.styles LIKE '%" . $style . "%'";
                }
                $query .= ") ORDER BY likes DESC) a UNION SELECT * FROM (SELECT * FROM posts WHERE deleted = 0 ORDER BY id DESC) b) c LIMIT " . ($pageIndex * $count) . ", " . $count;
            }

            $query = $this->db->query($query);
            return $query->getResultArray();
        } else {

            if ($fcount == 0) {
                $postData = $this->builder
                    ->select('posts.* ')
                    ->join('users', "users.id = posts.added_by")
                    ->join('blocked_users', "posts.added_by = blocked_users.user_id", 'left')
                    ->where('users.pronouns !=', "He")
                    ->where("blocked_users.user_id IS NULL")
                    ->where('deleted', 0)
                    ->orderBy('likes', 'DESC')
                    ->get($count, $pageIndex * $count)
                    ->getResultArray();

                return $postData;
            } else {
                $postData = $this->builder
                    ->select('posts.* ')
                    ->join('users', "users.id = posts.added_by")
                    ->join('blocked_users', "posts.added_by = blocked_users.user_id", 'left')
                    ->where('users.pronouns !=', "He")
                    ->where("blocked_users.user_id IS NULL")
                    ->where('deleted', 0)
                    ->orderBy('likes', 'DESC')
                    ->get($count, $pageIndex * $count)
                    ->getResultArray();

                return $postData;
            }
        }
    }

    public function getById($id)
    {
        $post = $this->builder
            ->where('id', $id)
            ->where('deleted', 0)
            ->get()
            ->getRowArray();
        if (!empty($post)) {
            $mentions = $this->db->table('posts_mentions')->where('post_id', $id)->get()->getResultArray();
            $images = $this->postImagesBuilder->where('post_id', $id)->orderBy('index', 'ASC')
                ->get()->getResultArray();
            $post['mentions'] = $mentions;
            $post['images'] = count($images) > 0 ? $images : [];
        }



        return $post;
    }

    public function getByUser($userId)
    {
        $postData = $this->builder
            ->where('added_by', $userId)
            ->where('deleted', 0)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $postData;
    }


    function checkMyLike($userId, $postid)
    {
        $builder = $this->db->table('likes');
        $builder->where(['user_id' => $userId, 'post_id' => $postid]);
        return $builder->get()->getNumRows();
    }

    function increaseViewCount($postIds)
    {
        $this->db->transStart();
        foreach ($postIds as $id) {
            $idInt = intval($id);
            if ($idInt == 0) {
                continue;
            }
            $this->builder->where('id', $idInt);
            $this->builder->set("views", "views + 1", FALSE);
            $this->builder->update();
        }

        $this->db->transComplete();
    }

    function setLike($data, $isLike)
    {

        $builder = $this->db->table('likes');
        $query = $builder->where($data)
            ->get();
        if ($query->getNumRows() > 0) {
            if ($isLike == 0) {
                $builder->delete($data);
                $this->updateLikeCount($data['post_id'], $isLike);
            }
        } else {
            $builder->insert($data);
            $this->updateLikeCount($data['post_id'], $isLike);
        }
    }

    function updateLikeCount($postId, $isLike)
    {

        if ($isLike == 1) {
            $this->builder->where('id', $postId);
            $this->builder->set('likes', 'likes + 1', FALSE);
            $this->builder->update();
        } else {
            $query = $this->builder->where('id', $postId)->get();
            if ($query->getNumRows() > 0) {
                $this->builder->where('id', $postId);
                $this->builder->set('likes', 'likes - 1', FALSE);
                $this->builder->update();
            }
        }
    }


    function addComment($data)
    {
        // Updated below to be users that are mentioned.
        $mentionedUsers = array();

        $this->db->transStart();
        $builder = $this->db->table('comments');
        $builder->insert($data);
        $commentId = $this->db->insertID();
        $comment = $data['comment'];
        // Update comment mentions
        $mentionedUsernames = Helpers::getUsernamesFromMentions($comment);
        try {
            if (count($mentionedUsernames) > 0) {
                $mentionedUsers = $this->_userModel->getUsersByUsername($mentionedUsernames);
                $mentionedUsersMetadata = array();
                foreach ($mentionedUsers as $mentionedUser) {
                    $insertData = array(
                        'user_id' => $mentionedUser['id'],
                        'comment_id' => $commentId,
                        'username' => $mentionedUser['username']
                    );
                    array_push($mentionedUsersMetadata, $insertData);
                }
                if (count($mentionedUsersMetadata) > 0) {
                    $this->db->table('comments_mentions')->insertBatch($mentionedUsersMetadata);
                }

            }
        } catch (Exception $e) {
            $f = $e;
        }

        $this->db->transComplete();
        return $mentionedUsers;

    }

    function deleteComment($commentId)
    {
        $builder = $this->db->table('comments')->where('id', $commentId);
        $builder->set('is_deleted', 1, FALSE);
        $builder->update();
    }

    function comments($postId)
    {
        $builder = $this->db->table('comments');
        $comments = $builder->where('post_id', $postId)
            ->where('is_deleted', FALSE)
            ->get()
            ->getResultArray();
        for ($i = 0; $i < count($comments); $i++) {
            $comment = $comments[$i];
            $mentions = $this->db->table('comments_mentions')->where('comment_id', $comment['id'])->get()->getResultArray();
            $comments[$i]['mentions'] = $mentions;
        }
        return $comments;
    }

    function searchPosts($keyword, $userId /*, $pageIndex, $count*/)
    {
        $termQuery = "SELECT UPPER(mapped_term) AS mapped_term_upper FROM search_terms WHERE UPPER(base_term) LIKE UPPER('%" . $this->db->escapeString($keyword, true) . "%')";
        $termResult = $this->db->query($termQuery);
        $strings = array();
        if ($termResult->getNumRows() > 0) {
            foreach ($termResult->getResultArray() as $row) {
                $strings[] = $row["mapped_term_upper"];
            }
        }
        $orComp = array();
        foreach ($strings as $string) {
            $orComp[] = "OR UPPER(caption) LIKE UPPER('%" . $this->db->escapeString($string, true) . "%')";
        }
        $orQuery = implode(" ", $orComp);
        $mainQuery = "
    SELECT posts.* FROM posts
 JOIN users
    on users.id = posts.added_by
    WHERE posts.id IN (
        SELECT id
        FROM posts
        WHERE added_by != '" . $userId . "'
        AND deleted = 0
        AND (
            UPPER(caption) LIKE UPPER('%" . $this->db->escapeString($keyword, true) . "%')
            OR UPPER(hashtag) LIKE UPPER('%" . $this->db->escapeString($keyword, true) . "%')
            $orQuery
        )
        GROUP BY posts.id
    ) AND users.pronouns != 'He'
    ORDER BY posts.id DESC;
";

        $query = $this->db->query($mainQuery);
        return $query->getResultArray();

    }

    function otherPosts($userId)
    {

        $postData = $this->builder
            ->select('posts.* ')
            ->join('users', "users.id = posts.added_by")
            ->join('blocked_users', "posts.added_by = blocked_users.user_id", 'left')
            ->where('users.pronouns !=', "He")
            ->where("blocked_users.user_id IS NULL")
            ->where('deleted', 0)
            ->where('added_by !=', $userId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $postData;
    }

    function likedPosts($userId, $pageIndex, $count)
    {

        $postData = $this->builder->select('posts.*')
            ->join('likes', 'posts.id = likes.post_id')
            ->where('posts.deleted', 0)
            ->where('likes.user_id', $userId)
            ->orderBy('likes.id', 'DESC')
            ->get($count, $pageIndex * $count)
            ->getResultArray();

        return $postData;
    }
}
