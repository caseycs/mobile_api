<?php
namespace Test\MobileApi\Unit;

require __DIR__ . '/../Controller/Ping.php';
require __DIR__ . '/../Message/Request/Ping/1.php';
require __DIR__ . '/../Message/Request/Ping/2.php';
require __DIR__ . '/../Message/Response/Pong/1.php';
require __DIR__ . '/../Message/Response/Error/1.php';
require __DIR__ . '/Handler.php';

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /* @var \MobileApi\Application */
    private $Application;

    public function setUp()
    {
        $this->Application = new \MobileApi\Application;
        $this->Application->setControllerPrefix('Test\MobileApi\Controller');
        $this->Application->setControllers(array('Ping'));
        $this->Application->setMessageRequestPrefix('Test\MobileApi\Message\Request');
    }

    public function provider_handle_ping()
    {
        $uri = 'http://localhost/Ping/1';
        $result = array();


        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array()
        );
        $result[] = array($Request, false, 'get params');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('json' => json_encode(array()))
        );
        $result[] = array($Request, false, 'get json encoded empty array');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('json' => '')
        );
        $result[] = array($Request, false, 'get json empty string');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST'
        );
        $result[] = array($Request, false, 'post params');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('json' => json_encode(array()))
        );
        $result[] = array($Request, false, 'post json encoded empty array');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('json' => '')
        );
        $result[] = array($Request, false, 'post json empty string');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array(),
            array(),
            array(),
            array(),
            bson_encode(array())
        );
        $result[] = array($Request, true, 'post bson');

        return $result;
    }

    /**
     * @dataProvider provider_handle_ping
     */
    public function test_handle_ping(\Symfony\Component\HttpFoundation\Request $Request, $bson, $comment)
    {
        $this->Application->useBSON($bson);
        $Response = $this->Application->handle($Request);
        $this->assertSame(200, $Response->getStatusCode(), $comment);
        $response = $bson ? bson_decode($Response->getContent()) : json_decode($Response->getContent(), true);
        $this->assertSame(
            array('message' => 'Pong', 'body' => array('content' => 'Pong')),
            $response,
            $comment
        );
    }

    public function provider_preHandler()
    {
        $uri = 'http://localhost/Ping/2';
        $result = array();

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array()
        );
        $result[] = array($Request, array('message' => 'Pong', 'body' => array('content' => 'Pong')), 'normal request');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('test' => 1)
        );
        $result[] = array($Request, array('message' => 'Pong', 'body' => array('content' => 'Test 1')), 'response from handler');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('test' => 2)
        );
        $result[] = array($Request, array('message' => 'Error', 'body' => array('code' => 1, 'msg' => 'test error')), 'error form handler');

        return $result;
    }

    /**
     * @dataProvider provider_preHandler
     */
    public function test_preHandler(\Symfony\Component\HttpFoundation\Request $Request, array $message, $comment) {
        $this->Application->setPreHandler(new Handler());

        $Response = $this->Application->handle($Request);
        $this->assertSame(200, $Response->getStatusCode(), $comment);
        $response = json_decode($Response->getContent(), true);
        $this->assertSame(
            $message,
            $response,
            $comment
        );
    }
}
