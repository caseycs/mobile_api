<?php
namespace Test\MobileApi\Message\Request;

class Ping_1 implements \MobileApi\Message\Request\RequestInterface
{
    public function getStructure()
    {
        return array();
    }

    public function getAvailableResponses()
    {
        return array(
            'Test\MobileApi\Message\Response\Pong_1',
        );
    }
}
