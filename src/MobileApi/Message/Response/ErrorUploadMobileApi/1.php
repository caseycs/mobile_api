<?php
namespace MobileApi\Message\Response;

use MobileApi\Message\Field;

class ErrorUploadMobileApi_1 implements \MobileApi\Message\Response\ResponseInterface
{
    const NOT_UPLOAD_INTERFACE = 1;
    const FILE_NOT_FOUND = 2;
    const WRONG_FILED = 3;
    const UPLOAD_ERROR = 4;

    public $code, $message;

    public function getName()
    {
        return 'ErrorUploadMobileApi';
    }

    public function getStructure()
    {
        return array(
            'code' => array(
                Field::REQUIRED,
                Field::ENUM,
                array(
                    self::NOT_UPLOAD_INTERFACE,
                    self::FILE_NOT_FOUND,
                    self::WRONG_FILED,
                    self::UPLOAD_ERROR,
                )
            ),
            'message' => array(Field::REQUIRED, Field::STRING),
        );
    }
}
