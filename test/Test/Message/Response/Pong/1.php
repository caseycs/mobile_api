<?php
namespace Test\Message\Response;

class Pong_1 implements \MobileApi\Message\Response\ResponseInterface
{
    public $content;

    public function getName()
    {
        return 'Pong';
    }

    public function getStructure()
    {
        return array(
            'content' => array(\MobileApi\Message\Field::REQUIRED, \MobileApi\Message\Field::STRING),
        );
    }
}
