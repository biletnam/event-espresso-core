<?php

namespace EventEspresso\tests\testcases\core\request_stack;

use EE_Request;
use PHPUnit_Framework_TestCase;

defined('EVENT_ESPRESSO_VERSION') || exit;


/**
 * Class RequestTest
 * Description
 *
 * @package EventEspresso\tests\testcases\core\request_stack
 * @author  Brent Christensen
 * @since   $VID:$
 */
class RequestTest extends PHPUnit_Framework_TestCase
{

    public function getParams($params = array())
    {
        return $params + array(
            'action' => 'edit',
            'id'     => 123,
        );
    }

    public function postParams($params = array())
    {
        return $params + array(
            'input-a' => 'A',
            'input-b' => 'B',
            'sub' => array(
                'sub-a'   => 'AA',
                'sub-b'   => 'BB',
                'sub-sub' => array(
                    'sub-sub-a'   => 'AAA',
                    'sub-sub-b'   => 'BBB',
                )
            ),
        );
    }

    public function cookieParams($params = array())
    {
        return $params + array(
            'PHPSESSID'   => 'abcdefghijklmnopqrstuvwxyz',
            'cookie_test' => 'a1b2c3d4e5f6g7h8i9j0.12345678',
        );
    }

    public function testGetParams()
    {
        $request = new EE_Request(
            $this->getParams(),
            array(),
            array()
        );
        $this->assertEquals(
            $this->getParams(),
            $request->get_params()
        );
    }

    public function testPostParams()
    {
        $request = new EE_Request(
            array(),
            $this->postParams(),
            array()
        );
        $this->assertEquals(
            $this->postParams(),
            $request->post_params()
        );
    }

    public function testCookieParams()
    {
        $request = new EE_Request(
            array(),
            array(),
            $this->cookieParams()
        );
        $this->assertEquals(
            $this->cookieParams(),
            $request->cookie_params()
        );
    }

    public function testParams()
    {
        $request = new EE_Request(
            $this->getParams(),
            $this->postParams(),
            array()
        );
        $this->assertEquals(
            array_merge($this->getParams(), $this->postParams()),
            $request->params()
        );
    }

    public function testSet()
    {
        $request = new EE_Request(
            $this->getParams(),
            array(),
            array()
        );
        $key = 'new-key';
        $value = 'ima noob';
        $request->set($key, $value);
        $params = $request->params();
        $this->assertArrayHasKey($key, $params);
        $this->assertEquals($value, $params[$key]);
    }

    public function testSetEE()
    {
        $request = new EE_Request(
            $this->getParams(),
            array(),
            array()
        );
        $request->set('ee', 'module-route');
        $params = $request->params();
        $this->assertArrayHasKey('ee', $params);
        $this->assertEquals('module-route', $params['ee']);
    }

    public function testAlreadySetEE()
    {
        $request = new EE_Request(
            $this->getParams(array('ee' => 'existing-route')),
            array(),
            array()
        );
        $request->set('ee', 'module-route');
        $params = $request->params();
        $this->assertArrayHasKey('ee', $params);
        $this->assertNotEquals('module-route', $params['ee']);
        $this->assertEquals('existing-route', $params['ee']);
    }

    public function testOverrideAlreadySetEE()
    {
        $request = new EE_Request(
            $this->getParams(array('ee' => 'existing-route')),
            array(),
            array()
        );
        $request->set('ee', 'module-route', true);
        $params = $request->params();
        $this->assertArrayHasKey('ee', $params);
        $this->assertEquals('module-route', $params['ee']);
    }



    public function testGet()
    {
        $request = new EE_Request(
            $this->getParams(),
            array(),
            array()
        );
        // key exists
        $this->assertEquals('edit', $request->get('action'));
        // key does NOT exist and no default value set
        $this->assertNotEquals('edit', $request->get('me-no-key'));
        $this->assertNull($request->get('me-no-key'));
        // key does NOT exist but default value set
        $this->assertNotEquals(
            'edit',
            $request->get('me-no-key', 'me-default')
        );
        $this->assertEquals(
            'me-default',
            $request->get('me-no-key', 'me-default')
        );
    }



    public function testGetWithDrillDown()
    {
        $request = new EE_Request(
            array(),
            $this->postParams(),
            array()
        );
        // our post data looks like this:
        //  array(
        //    'input-a' => 'A',
        //    'input-b' => 'B',
        //    'sub' => array(
        //        'sub-a'   => 'AA',
        //        'sub-b'   => 'BB',
        //        'sub-sub' => array(
        //            'sub-sub-a'   => 'AAA',
        //            'sub-sub-b'   => 'BBB',
        //          )
        //      )
        //  );
        // top-level value
        $this->assertEquals('A', $request->get('input-a'));
        $this->assertEquals('B', $request->get('input-b'));
        // second level
        $this->assertEquals('AA', $request->get('sub[sub-a]'));
        $this->assertEquals('BB', $request->get('sub[sub-b]'));
        // third level
        $this->assertEquals('AAA', $request->get('sub[sub-sub][sub-sub-a]'));
        $this->assertEquals('BBB', $request->get('sub[sub-sub][sub-sub-b]'));
        // does not exist
        $this->assertNull($request->get('input-c'));
        $this->assertNull($request->get('sub[sub-c]'));
        $this->assertNull($request->get('sub[sub-sub][sub-sub-c]'));
    }



    public function testIsSet()
    {
        $request = new EE_Request(
            $this->getParams(),
            array(),
            array()
        );
        $this->assertTrue($request->is_set('action'));
        $this->assertFalse($request->is_set('me-no-key'));
    }



    public function testIsSetWithDrillDown()
    {
        $request = new EE_Request(
            array(),
            $this->postParams(),
            array()
        );
        // our post data looks like this:
        //  array(
        //    'input-a' => 'A',
        //    'input-b' => 'B',
        //    'sub' => array(
        //        'sub-a'   => 'AA',
        //        'sub-b'   => 'BB',
        //        'sub-sub' => array(
        //            'sub-sub-a'   => 'AAA',
        //            'sub-sub-b'   => 'BBB',
        //          )
        //      )
        //  );
        // top-level value
        $this->assertTrue($request->is_set('input-a'));
        $this->assertTrue($request->is_set('input-b'));
        // second level
        $this->assertTrue($request->is_set('sub[sub-a]'));
        $this->assertTrue($request->is_set('sub[sub-b]'));
        // third level
        $this->assertTrue($request->is_set('sub[sub-sub][sub-sub-a]'));
        $this->assertTrue($request->is_set('sub[sub-sub][sub-sub-b]'));
        // not set
        $this->assertFalse($request->is_set('input-c'));
        $this->assertFalse($request->is_set('sub[sub-c]'));
        $this->assertFalse($request->is_set('sub[sub-sub][sub-sub-c]'));
    }



    public function testUnSet()
    {
        // do the chevy shuffle with the $_REQUEST global
        // in case it's needed by other tests
        $EXISTING_REQUEST = $_REQUEST;
        $_REQUEST = $this->getParams();
        $request = new EE_Request(
            $_REQUEST,
            array(),
            array()
        );
        $this->assertTrue($request->is_set('action'));
        $request->un_set('action');
        $this->assertFalse($request->is_set('action'));
        $this->assertTrue(isset($_REQUEST['action']));
        // unset 'id' param but from GLOBAL too
        $this->assertTrue($request->is_set('id'));
        $request->un_set('id', true);
        $this->assertFalse($request->is_set('id'));
        $this->assertFalse(isset($_REQUEST['id']));
        // reinstate $_REQUEST global
        $_REQUEST = $EXISTING_REQUEST;
    }



    public function testIpAddress()
    {
        // do the chevy shuffle with the $_SERVER global
        // in case it's needed by other tests
        $EXISTING_SERVER = $_SERVER;
        $server_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );
        $x = 0;
        // let's test 100 random IP addresses
        while ($x < 100) {
            // first clear out entries from previous test
            foreach ($server_keys as $server_key) {
                unset($_SERVER[$server_key]);
            }
            // randomly generate IP address. plz see: https://stackoverflow.com/a/39846883
            $ip_address = long2ip(mt_rand() + mt_rand() + mt_rand(0, 1));
            // then randomly populate one of the $_SERVER keys used to determine the IP
            $_SERVER[$server_keys[mt_rand(0, 6)]] = $ip_address;
            $request = new EE_Request(array(), array(), array());
            $this->assertEquals($ip_address, $request->ip_address());
            unset($request);
            $x++;
        }
        // reinstate $_SERVER global
        $_SERVER = $EXISTING_SERVER;
    }

}
// Location: tests/testcases/core/request_stack/RequestTest.php
