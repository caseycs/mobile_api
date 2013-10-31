<?php
namespace MobileApi\Message\Response;

use MobileApi\Message\Field;

/**
 * Global mobile api error, can happen anytime and should by handled on client correct.
 *
 * enum.code.1
 * internal server error
 *
 * enum.code.100
 * unknown command
 *
 * enum.code.101
 * controller not found
 *
 * enum.code.102
 * request class not found
 *
 * enum.code.103
 * request decode fail
 *
 * enum.code.104
 * request invalid
 *
 * enum.code.105
 * response class invalid
 *
 * enum.code.106
 * response class is not appropriate for request class
 *
 * enum.code.107
 * response invalid
 *
 * property.message
 * text explanation
 */
class ErrorMobileApi_1 implements \MobileApi\Message\Response\ResponseInterface
{
    //common errors
    const SERVER_ERROR = 1;

    //front controller
    const UNKOWN_COMMAND = 100;
    const CONTROLLER_NOT_FOUND = 101;
    const REQUEST_CLASS_NOT_FOUND = 102;
    const REQUEST_DECODE_FAIL = 103;
    const REQUEST_INVALID = 104;
    const RESPONSE_CLASS_INVALID = 105;
    const REQUEST_RESPONSE_NOT_APPROPRIATE = 106;
    const RESPONSE_INVALID = 107;

    public $code, $message;

    public function getName()
    {
        return 'ErrorMobileApi';
    }

    public function getStructure()
    {
        return array(
            'code' => array(
                Field::REQUIRED,
                Field::ENUM,
                array(
                    self::SERVER_ERROR,
                    self::UNKOWN_COMMAND,
                    self::CONTROLLER_NOT_FOUND,
                    self::REQUEST_CLASS_NOT_FOUND,
                    self::REQUEST_DECODE_FAIL,
                    self::REQUEST_INVALID,
                    self::RESPONSE_CLASS_INVALID,
                    self::REQUEST_RESPONSE_NOT_APPROPRIATE,
                    self::RESPONSE_INVALID,
                )
            ),
            'message' => array(Field::REQUIRED, Field::STRING),
        );
    }
}
