<?php
class Minim_Auth_Orm_Backend implements Minim_Auth_Backend
{
    var $_auth;
    var $_orm;

    function __construct($params, &$auth)
    {
        $this->_auth =& $auth;
        $this->_orm =& minim('orm');
        if (!$this->_orm->_backend)
        {
            throw new Minim_Auth_Exception(
                "ORM Auth backend error: ORM not configured");
        }
        try
        {
            $this->_orm->register('user')
                       ->int('id', array('auto_increment' => TRUE))
                       ->text('name', array('max_length' => 20))
                       ->text('password_hash', array('max_length' => 32));
        }
        catch (Minim_Orm_Exception $moe)
        {
            // model already registered
            // TODO - could be a problem
        }
    }

    function login($username, $password)
    {
        try
        {
            $user_do =& $this->_orm->user->get(array(
                'name' => $username,
                'password_hash' => md5("S4lt~$username:$password-p3PPeR")
            ));
        }
        catch (Minim_Orm_Exception $moe)
        {
            return NULL;
        }
        return new Minim_User($username, $password, $this->_auth);
    }

    function logout($user)
    {
        // TODO
    }

    function logged_in($user)
    {
        return TRUE;
    }

    function allow($user, $action)
    {
        return TRUE;
    }
}
