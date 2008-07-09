<?php
class Blog
{
    function getRecentPosts($num)
    {
        $sql = <<<SQL
            SELECT *
            FROM post
            ORDER BY posted DESC
            LIMIT :n
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute(array(':n' => $num));
        return $s->fetchAll();
    }

    function getPost($year, $month, $day, $slug)
    {
        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $params = array(
            ':from' => "$date 00:00:00",
            ':to' => "$date 23:59:59",
            ':slug' => $slug
        );
        $sql = <<<SQL
            SELECT *
            FROM post
            WHERE slug LIKE :slug AND
                  posted BETWEEN :from AND :to
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute($params);
        return $s->fetch();
    }
}
