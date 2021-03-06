<?php
namespace Test\MobileApi\Unit;

use Symfony\Component\HttpFoundation\File\UploadedFile;

require_once __DIR__ . '/../Controller/Ping.php';
require_once __DIR__ . '/../Controller/Upload.php';
require_once __DIR__ . '/../Message/Request/Ping/1.php';
require_once __DIR__ . '/../Message/Request/Upload/1.php';
require_once __DIR__ . '/../Message/Request/Ping/2.php';
require_once __DIR__ . '/../Message/Response/Pong/1.php';
require_once __DIR__ . '/../Message/Response/Upload/1.php';
require_once __DIR__ . '/../Message/Response/Error/1.php';
require_once __DIR__ . '/../Handler/HandlerTestPong.php';
require_once __DIR__ . '/../Handler/HandlerTestError.php';

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /* @var \MobileApi\Application */
    private $Application;

    public function setUp()
    {
        $this->Application = new \MobileApi\Application;
        $this->Application->setControllerPrefix('Test\MobileApi\Controller');
        $this->Application->setControllers(array('Ping', 'Upload'));
        $this->Application->setMessageRequestPrefix('Test\MobileApi\Message\Request');
        $this->Application->setMessageResponsePrefix('Test\MobileApi\Message\Response');
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
        $result[] = array($Request, 'get params');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('json' => json_encode(array()))
        );
        $result[] = array($Request, 'get json encoded empty array');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'GET',
            array('json' => '')
        );
        $result[] = array($Request, 'get json empty string');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST'
        );
        $result[] = array($Request, 'post params');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('json' => json_encode(array()))
        );
        $result[] = array($Request, 'post json encoded empty array');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('json' => '')
        );
        $result[] = array($Request, 'post json empty string');

        return $result;
    }

    /**
     * @dataProvider provider_handle_ping
     */
    public function test_handle_ping(\Symfony\Component\HttpFoundation\Request $Request, $comment)
    {
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame(200, $Response->getStatusCode(), $comment);
        $response = json_decode($Response->getContent(), true);
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
        $this->Application->addPreHandler(new \Test\MobileApi\Handler\HandlerTestPong());
        $this->Application->addPreHandler(new \Test\MobileApi\Handler\HandlerTestError());

        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame(200, $Response->getStatusCode(), $comment);
        $response = json_decode($Response->getContent(), true);
        $this->assertSame(
            $message,
            $response,
            $comment
        );
    }

    public function provider_uploadFile()
    {
        $uri = 'http://localhost/Upload/1';
        $result = array();
        $file = new UploadedFile(__DIR__ . '/../../../test.jpg', 'testFile', null, null, null, true);

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            'http://localhost/Ping/2',
            'POST',
            array(),
            array(),
            array('file' => $file)
        );
        $result[] = array($Request, array('message' => 'ErrorUploadMobileApi', 'body' => array('code' => 1, 'message' => 'request not implement upload interface')), 400, 'get request not implements upload interface');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            'http://localhost/Ping/2',
            'POST',
            array(),
            array(),
            array('file' => $file)
        );
        $result[] = array($Request, array('message' => 'ErrorUploadMobileApi', 'body' => array('code' => 1, 'message' => 'request not implement upload interface')), 400, 'post request not implements upload interface');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('name' => 'name1'),
            array()
        );
        $result[] = array($Request, array('message' => 'ErrorUploadMobileApi', 'body' => array('code' => 2, 'message' => 'file in request not found')), 400, 'file not found');

        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('name' => 'name1'),
            array(),
            array('image' => $file)
        );
        $result[] = array($Request, array('message' => 'ErrorUploadMobileApi', 'body' => array('code' => 3, 'message' => 'wrong field, expecting file')), 400, 'wrong field');


        $Request = \Symfony\Component\HttpFoundation\Request::create(
            $uri,
            'POST',
            array('name' => 'name1'),
            array(),
            array('file' => $file)
        );
        $result[] = array($Request, array('message' => 'Upload', 'body' => array('name' => 'testFile', 'size' => filesize($file->getRealPath()))), 200, 'normal upload');

        return $result;
    }

    /**
     * @dataProvider provider_uploadFile
     */
    public function test_uploadFile(\Symfony\Component\HttpFoundation\Request $Request, array $message, $code, $comment)
    {
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame($code, $Response->getStatusCode(), $comment . ' [' . $Response->getContent()  .']');
        $response = json_decode($Response->getContent(), true);
        $this->assertSame(
            $message,
            $response,
            $comment
        );
    }
}
