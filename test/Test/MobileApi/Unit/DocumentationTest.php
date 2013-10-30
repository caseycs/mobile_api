<?php
namespace Test\MobileApi\Unit;

use Symfony\Component\HttpFoundation\File\UploadedFile;

require_once __DIR__ . '/../Controller/Ping.php';
require_once __DIR__ . '/../Message/Request/Ping/1.php';
require_once __DIR__ . '/../Message/Request/Ping/2.php';
require_once __DIR__ . '/../Message/Response/Pong/1.php';

class DocumentationTest extends \PHPUnit_Framework_TestCase
{
    /* @var \MobileApi\Application */
    private $Application;

    public function setUp()
    {
        $this->Application = new \MobileApi\Application;
        $this->Application->setControllerPrefix('Test\MobileApi\Controller');
        $this->Application->setControllers(array('Ping'));
        $this->Application->setMessageRequestPrefix('Test\MobileApi\Message\Request');
        $this->Application->setMessageResponsePrefix('Test\MobileApi\Message\Response');
        $this->Application->setDocumentation('_doc', array('Ping_1', 'Ping_2'), array('Pong_1'));
    }

    public function test_documentation_not_exists()
    {
        $this->Application = new \MobileApi\Application;
        $this->Application->setControllerPrefix('Test\MobileApi\Controller');
        $this->Application->setControllers(array('Ping'));
        $this->Application->setMessageRequestPrefix('Test\MobileApi\Message\Request');
        $this->Application->setMessageResponsePrefix('Test\MobileApi\Message\Response');

        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame(404, $Response->getStatusCode());
    }

    public function test_documentation_index()
    {
        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertContains('Ping_1', $Response->getContent());
        $this->assertContains('Ping_2', $Response->getContent());
        $this->assertNotContains('Pong_1', $Response->getContent());
    }

    public function test_documentation_request()
    {
        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc?request=Ping_1');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertContains('Ping_1', $Response->getContent());
        $this->assertContains('Pong_1', $Response->getContent());
        $this->assertNotContains('Ping_2', $Response->getContent());
    }

    public function test_documentation_request_not_found()
    {
        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc?request=Ping_10');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame(404, $Response->getStatusCode());
    }

    public function test_documentation_response()
    {
        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc?response=Pong_1');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertContains('Pong_1', $Response->getContent());
        $this->assertNotContains('Ping_1', $Response->getContent());
        $this->assertNotContains('Ping_2', $Response->getContent());
    }

    public function test_documentation_response_not_found()
    {
        $Request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/_doc?response=Pong_10');
        $Response = $this->Application->handle($Request, \MobileApi\Application::MASTER_REQUEST, false);
        $this->assertSame(404, $Response->getStatusCode());
    }
}
