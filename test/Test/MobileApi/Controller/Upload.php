<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 16.10.13
 * Time: 19:53
 * To change this template use File | Settings | File Templates.
 */

namespace Test\MobileApi\Controller;


use MobileApi\ControllerInterface;
use MobileApi\Message\Request\RequestInterface;
use Test\MobileApi\Message\Request\Upload_1;

class Upload implements ControllerInterface {

    /**
     * @param RequestInterface|Upload_1 $Request
     */
    function run(RequestInterface $Request)
    {
        $file = $Request->getFile();

        $Response = new \Test\MobileApi\Message\Response\Upload_1();
        $Response->name = $file->getClientOriginalName();
        $Response->size = $file->getSize();
        return $Response;
    }
}