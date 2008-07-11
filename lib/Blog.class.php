<?php
require minim()->lib('breve');

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
        $posts = array();
        foreach ($s->fetchAll() as $post)
        {
            $posts[] = new BlogPost($post);
        }
        return $posts;
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
        return new BlogPost($s->fetch());
    }
}

class BlogPost extends BreveModel
{
    function define()
    {
        $this->setField('id', new BreveInt(array('autoincrement' => TRUE)));
        $this->setField('title', new BreveChar(array('maxlength' => 100)));
        $this->setField('slug', new BreveSlug(array(
            'from' => $this->getField('title'))));
        $this->setField('content', new BreveText());
        $this->setField('posted', new BreveTimestamp());
        $this->setField('author', new BreveInt());
        $this->setField('tags', new BreveChar(array('maxlength' => 255)));
    }

    function __get($name)
    {
        if ($name == 'comments')
        {
            return $this->getComments();
        }
        return parent::__get($name);
    }

    function getComments()
    {
        static $comments;
        if (!is_array($comments))
        {
            $sql = <<<SQL
                SELECT *
                FROM comment
                WHERE post_id=:id
                ORDER BY posted DESC
SQL;
            $s = minim()->db()->prepare($sql);
            $s->execute(array(':id' => $this->id));
            $comments = array();
            foreach ($s->fetchAll() as $comment)
            {
                $comments[] = new BlogComment($comment);
            }
        }
        return $comments;
    }
}

class BlogComment extends BreveModel
{
    function define()
    {
        $this->setField('id', new BreveInt(array('autoincrement' => TRUE)));
        $this->setField('post_id', new BreveInt());
        $this->setField('content', new BreveText());
        $this->setField('posted', new BreveTimestamp());
        $this->setField('author', new BreveInt());
    }
}
