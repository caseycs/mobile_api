<?php
namespace Test\Controller;

class Ping implements \MobileApi\ControllerInterface
{
    /* @var \Test\Message\Request\Ping_1 $Request */
    function run(\MobileApi\Message\Request\RequestInterface $Request)
    {
        $Response = new \Test\Message\Response\Pong_1();
        $Response->content = 'Pong';
        return $Response;
    }
}
