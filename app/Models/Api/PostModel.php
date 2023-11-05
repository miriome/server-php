<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class PostModel extends Model
{

    protected $table = 'posts';
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

    public function editPost($postId, $data)
    {
        $this->builder->where('id', $postId)
            ->set($data)
            ->update();
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


    public function getPosts($pageIndex, $count, $userId, $fcount)
    {

        if ($fcount == 0) {

            $postData = $this->builder
                ->select('posts.* ')
                ->join('users', "users.id = posts.added_by")
                ->join('blocked_users', "posts.added_by = blocked_users.user_id", 'left')
                ->where("blocked_users.user_id IS NULL")
                ->where('users.pronouns !=', "He")
                ->where("(added_by IN (SELECT `target_id` FROM follow WHERE `user_id` = $userId) OR added_by = $userId)")
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
                ->where("(added_by IN (SELECT `target_id` FROM follow WHERE `user_id` = $userId) OR added_by = $userId)")
                ->where('deleted', 0)
                ->orderBy('id', 'DESC')
                ->get($count, $pageIndex * $count)
                ->getResultArray();

            return $postData;

        }
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
                $query .= ") ORDER BY id DESC) a UNION SELECT * FROM (SELECT * FROM posts WHERE deleted = 0 ORDER BY id DESC) b) c LIMIT " . ($pageIndex * $count) . ", " . $count;
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
                    ->orderBy('id', 'DESC')
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
        return $post;
    }

    public function getByUser($userId)
    {
        $postData = $this->builder
            ->where('added_by', $userId)
            ->where('deleted', 0)
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
        $builder = $this->db->table('comments');
        $builder->insert($data);
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
        return $builder->where('post_id', $postId)
            ->where('is_deleted', FALSE)
            ->get()
            ->getResultArray();
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
    ORDER BY chat_enabled DESC, posts.id DESC;
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
            ->orderBy('chat_enabled', 'DESC')
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
