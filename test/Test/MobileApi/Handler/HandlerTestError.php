<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 10.10.13
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

namespace Test\MobileApi\Handler;


use MobileApi\ControllerInterface;
use MobileApi\HandlerInterface;
use MobileApi\Message\Request\RequestInterface;
use MobileApi\Message\Response\ResponseInterface;
use Test\MobileApi\Message\Request\Ping_2;

class HandlerTestError implements HandlerInterface {

    /**
     * @param ControllerInterface $Controller
     * @param \MobileApi\Message\Request\RequestInterface|\Test\MobileApi\Message\Request\Ping_2 $Request
     * @return null|ResponseInterface
     */
    public function run(ControllerInterface $Controller, RequestInterface $Request)
    {
        if($Request->test == 2) {
            $Response = new \Test\MobileApi\Message\Response\Error_1();
            $Response->code = 1;
            $Response->msg = 'test error';
            return $Response;
        }
    }
}
