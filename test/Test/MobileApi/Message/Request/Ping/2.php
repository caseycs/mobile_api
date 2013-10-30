<?php
namespace Test\MobileApi\Message\Request;

class Ping_2 implements \MobileApi\Message\Request\RequestInterface
{
    public $test;

    public function getStructure()
    {
        return array(
            'test' => array(\MobileApi\Message\Field::OPTIONAL, \MobileApi\Message\Field::INTEGER),
        );
    }

    public function getAvailableResponses()
    {
        return array(
            'Pong_1',
            'Error_1',
        );
    }
}
