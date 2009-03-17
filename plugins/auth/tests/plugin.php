<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/auth/auth.php';

class Minim_Auth_TestCase extends TestCase
{
    function test_auth_set_backend()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $this->assertTrue($auth->_backend);
    }

    function test_auth_login_fail()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user =& $auth->login('test', 'test');
        $this->assertEqual(NULL, $user,
            "Failed login should return NULL, but got ".print_r($user, TRUE));
    }

    function test_auth_logout_not_logged_in()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = new Minim_User('test', 'test', $auth);
        try
        {
            $user->logout();
        }
        catch (Minim_Auth_Exception $mae)
        {
            // test passes
            return;
        }
        $this->fail("Expected exception");
    }

    function test_auth_permission()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = new Minim_User('test', 'test', $auth);
        $this->assertTrue(!$user->can('access admin'),
            "User should not have permission to access admin");
    }

}
