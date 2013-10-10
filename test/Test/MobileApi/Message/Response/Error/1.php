<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 10.10.13
 * Time: 12:02
 * To change this template use File | Settings | File Templates.
 */

namespace Test\MobileApi\Message\Response;


use MobileApi\Message\Response\ResponseInterface;

class Error_1 implements ResponseInterface {

    public $code, $msg;

    function getStructure()
    {
        return array(
            'code' => array(\MobileApi\Message\Field::REQUIRED, \MobileApi\Message\Field::INTEGER),
            'msg' => array(\MobileApi\Message\Field::OPTIONAL, \MobileApi\Message\Field::STRING),
        );
    }

    function getName()
    {
        return 'Error';
    }
}