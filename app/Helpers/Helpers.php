<?php

namespace App\Helpers;

class Helpers
{
    public static function getUsernamesFromMentions(string $text)
    {
        $usernames = array();
        $splitString = explode(' ', $text);
        foreach ($splitString as $string) {
            if (substr($string, 0, strlen("@")) == "@") {
                array_push($usernames, substr($string, 1));
            }
        }
        return $usernames;

    }

    public static function getMentionMetadata(string $text, $user)
    {
        $position = strpos($text, "@{$user['username']}");
        /// It shouldnt be false.
        if ($position === false) {
            return NULL;
        }
        return array(
            'user_id' => $user['id'],
            'username' => $user['username']
        );

    }
}
