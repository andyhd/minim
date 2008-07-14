<?php
class BlogPost extends BreveModel
{
    function define()
    {
        $this->setField('id', new BreveInt(array(
            'autoincrement' => TRUE)));
        $this->setField('title', new BreveText(array(
            'maxlength' => 100,
            'required' => TRUE,
            'not_null' => TRUE)));
        $this->setField('slug', new BreveSlug(array(
            'read_only' => TRUE,
            'from' => $this->getField('title'))));
        $this->setField('content', new BreveText(array(
            'required' => TRUE,
            'not_null' => TRUE)));
        $this->setField('posted', new BreveTimestamp(array(
            'auto_now' => TRUE)));
        $this->setField('author', new BreveInt(array(
            'not_null' => TRUE)));
        $this->setField('tags', new BreveText(array(
            'maxlength' => 255)));
    }

    function comments()
    {
        return breve()->manager('BlogComment')->getForPost($this->id);
    }
}

class BlogPostManager extends BreveManager
{
    var $table = "post";

    function getRecent($num)
    {
        $sql = <<<SQL
            SELECT *
            FROM {$this->table}
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

    // override get
    function get($year, $month, $day, $slug)
    {
        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $params = array(
            ':from' => "$date 00:00:00",
            ':to' => "$date 23:59:59",
            ':slug' => $slug
        );
        $sql = <<<SQL
            SELECT *
            FROM {$this->table}
            WHERE slug LIKE :slug AND
                  posted BETWEEN :from AND :to
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute($params);
        return new BlogPost($s->fetch());
    }
}

class BlogComment extends BreveModel
{
    function define()
    {
        $this->setField('id', new BreveInt(array(
            'autoincrement' => TRUE)));
        $this->setField('post_id', new BreveInt(array(
            'not_null' => TRUE)));
        $this->setField('content', new BreveText(array(
            'required' => TRUE,
            'not_null' => TRUE)));
        $this->setField('posted', new BreveTimestamp(array(
            'auto_now' => TRUE)));
        $this->setField('name', new BreveText(array(
            'maxlength' => 100,
            'not_null' => TRUE,
            'required' => TRUE)));
        $this->setField('email', new BreveText(array(
            'maxlength' => 255)));
    }
}

class BlogCommentManager extends BreveManager
{
    var $table = "comment";

    function getForPost($post_id)
    {
        $sql = <<<SQL
            SELECT *
            FROM {$this->table}
            WHERE post_id=:id
            ORDER BY posted DESC
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute(array(':id' => $post_id));
        $comments = array();
        foreach ($s->fetchAll() as $comment)
        {
            $comments[] = new BlogComment($comment);
        }
        return $comments;
    }
}
