<?php
namespace MobileApi\Message\Response;

use MobileApi\Message\Field;

class ErrorMobileApi_1 implements \MobileApi\Message\Response\ResponseInterface
{
    //common errors
    const CODE_SERVER_ERROR = 1;

    //front controller
    const CODE_UNKOWN_COMMAND = 100;
    const CODE_CONTROLLER_NOT_FOUND = 101;
    const CODE_REQUEST_CLASS_NOT_FOUND = 102;
    const CODE_REQUEST_DECODE_FAIL = 103;
    const CODE_REQUEST_INVALID = 104;
    const CODE_RESPONSE_CLASS_INVALID = 105;
    const CODE_REQUEST_RESPONSE_NOT_APPROPRIATE = 106;
    const CODE_RESPONSE_INVALID = 107;

    public $code, $message;

    public function getName()
    {
        return 'ErrorMobileApi';
    }

    public function getStructure()
    {
        return array(
            'code' => array(Field::REQUIRED, Field::INTEGER),
            'message' => array(Field::REQUIRED, Field::STRING),
        );
    }
}
