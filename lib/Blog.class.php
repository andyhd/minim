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

    function __get($name)
    {
        if ($name == 'comments')
        {
            return $this->comments();
        }
        return parent::__get($name);
    }
}

class BlogPostManager extends BreveManager
{
    var $table = "post";

    function latest($num)
    {
        $ms = new BreveModelSet('BlogPost');
        $ms->order_by('-posted');
        $ms->limit($num);
        return $ms;
    }

    function all()
    {
        $ms = new BreveModelSet('BlogPost');
        $ms->order_by('-posted');
        return $ms;
    }

    // override get
    function get($year, $month, $day, $slug)
    {
        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $from = "$date 00:00:00";
        $to = "$date 23:59:59";
        $ms = new BreveModelSet('BlogPost');
        $ms->filter(array(
            'slug__eq' => $slug,
            'posted__range' => array($from, $to)
        ));
        $ms->limit(1);
        return $ms;
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
        $ms = new BreveModelSet('BlogComment');
        $ms->filter(array('post_id__eq' => $post_id));
        $ms->order_by('-posted');
        return $ms;
    }
}
