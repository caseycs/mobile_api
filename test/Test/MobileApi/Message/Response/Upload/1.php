<?php
namespace Test\MobileApi\Message\Response;

class Upload_1 implements \MobileApi\Message\Response\ResponseInterface
{
    public $name, $size;

    public function getName()
    {
        return 'Upload';
    }

    public function getStructure()
    {
        return array(
            'name' => array(\MobileApi\Message\Field::REQUIRED, \MobileApi\Message\Field::STRING),
            'size' => array(\MobileApi\Message\Field::REQUIRED, \MobileApi\Message\Field::INTEGER),
        );
    }
}
