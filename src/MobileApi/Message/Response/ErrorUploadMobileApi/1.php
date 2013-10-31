<?php
namespace MobileApi\Message\Response;

use MobileApi\Message\Field;

/**
 * When request contains file (enctype=multipart/form-data), it passes additinal validation.
 *
 * enum.code.1
 * request do not expect uploaded file
 *
 * enum.code.2
 * no file uploaded
 *
 * enum.code.3
 * file is uploaded not in "file" field
 *
 * enum.code.4
 * uploaded error
 *
 * enum.code.5
 * more then one file uploaded
 *
 * property.message
 * text explanation
 */
class ErrorUploadMobileApi_1 implements \MobileApi\Message\Response\ResponseInterface
{
    const NOT_UPLOAD_INTERFACE = 1;
    const FILE_NOT_FOUND = 2;
    const WRONG_FIELD = 3;
    const UPLOAD_ERROR = 4;
    const MANY_FILES_UPLOADED = 5;

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
                    self::WRONG_FIELD,
                    self::UPLOAD_ERROR,
                    self::MANY_FILES_UPLOADED,
                )
            ),
            'message' => array(Field::REQUIRED, Field::STRING),
        );
    }
}
