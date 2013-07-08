<?php
namespace Test\MobileApi\Controller;

class Ping implements \MobileApi\ControllerInterface
{
    /* @var \Test\MobileApi\Message\Request\Ping_1 $Request */
    function run(\MobileApi\Message\Request\RequestInterface $Request)
    {
        $Response = new \Test\MobileApi\Message\Response\Pong_1();
        $Response->content = 'Pong';
        return $Response;
    }
}
