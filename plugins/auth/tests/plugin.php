<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/auth/auth.php';

class Minim_Auth_TestCase extends TestCase
{
    function set_up()
    {
        $db =& minim('orm')->_backend;
        $db->execute_query(
            "CREATE TABLE IF NOT EXISTS user (id INTEGER PRIMARY KEY, name TEXT, password_hash TEXT)",
            array()
        );
        $db->execute_query(
            "INSERT INTO user (id, name, password_hash) VALUES (:id, :name, ".
            ":password_hash)",
            array(
                ':id' => 1,
                ':name' => 'test',
                ':password_hash' => md5("S4lt~test:test-p3PPeR")
            )
        );
    }

    function tear_down()
    {
        minim('orm')->_backend->execute_query('DELETE FROM user', array());
        minim('orm')->_backend->execute_query('DROP TABLE IF EXISTS user',
            array());
    }

    function test_auth_set_backend()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $this->assertTrue($auth->_backend);
    }

    function test_auth_parse_cookie()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $auth->encryption_key = 'password';
        $username = 'test';
        $ts = '20090322192500';
        $hash = md5("uid:$username,ts:$ts");
        $plain = "user=$username&timestamp=$ts&hash=$hash";
        $cookie = $auth->encrypt($plain);
        $GLOBALS['_COOKIE'] = array(
            'u' => $cookie
        );
        $this->assertEqual($plain, $auth->decrypt($cookie));
        $user = $auth->get_logged_in_user();
        $this->assertEqual($username, $user->username,
            "Parsing user cookie failed: ".dump($user));
    }

    function test_auth_login_fail()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user =& $auth->login('test2', 'test');
        $this->assertEqual(NULL, $user,
            "Failed login should return NULL, but got ".print_r($user, TRUE));
    }

    function test_auth_login()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = $auth->login('test', 'test');
        $this->assertEqual('test', $user->username);
        $this->assertEqual($user, $auth->get_logged_in_user());
    }

    function test_auth_logout_not_logged_in()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = new Minim_User('test2', $auth);
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

    function test_auth_logout()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = $auth->login('test', 'test');
        $user->logout();
    }

    function test_auth_permission()
    {
        $auth = new Minim_Auth();
        $auth->set_backend('orm');
        $user = new Minim_User('test', $auth);
        $this->assertTrue(!$user->can('access admin'),
            "User should not have permission to access admin");
    }

}
