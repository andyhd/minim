<?php
/**
 * Query objects
 *
 * Elements of a query
 * - action: Retrieve, Update, Delete (Create is not a query)
 * - subject: model
 * - criteria: boolean logic / set logic
 *   - x = y, x > y, x < y, etc
 *   - AND OR NOT
 *   - x IN y, x NOT IN y
 *   - x and y are EXPRESSIONS
 *
 * want syntax like:
 * $posts = $orm->post->all();
 * $posts->where("field")->gt($y)
 * $posts->where("field")->in(array())
 * $posts->where("field")->eq($y)->and("field")->ne($z);
 * $posts->where("field")->lt($x)->or("field")->gt($y);
 *
 * where(), and(), or() return query objects in parameter expectant state
 */
class Minim_Query_Object
{
    var $_resultset;

    function Minim_Query_Object(&$resultset)
    {
        $this->_resultset = $resultset;
    }

    function equals($
}
