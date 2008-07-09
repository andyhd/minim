<?php
class Blog
{
    function getRecentPosts($num)
    {
        $s = minim()->db()->prepare('SELECT * FROM post ORDER BY posted DESC LIMIT :n');
        $s->execute(array(':n' => $num));
        return $s->fetchAll();
    }
}
