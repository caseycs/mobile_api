<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 10.10.13
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

namespace Test\MobileApi\Unit;


use MobileApi\ControllerInterface;
use MobileApi\HandlerInterface;
use MobileApi\Message\Request\RequestInterface;
use MobileApi\Message\Response\ResponseInterface;
use Test\MobileApi\Message\Request\Ping_2;

class HandlerTestPong implements HandlerInterface {

    /**
     * @param ControllerInterface $Controller
     * @param \MobileApi\Message\Request\RequestInterface|\Test\MobileApi\Message\Request\Ping_2 $Request
     * @return null|ResponseInterface
     */
    public function run(ControllerInterface $Controller, RequestInterface $Request)
    {
        if($Request->test == 1) {
            $Response = new \Test\MobileApi\Message\Response\Pong_1();
            $Response->content = 'Test 1';
            return $Response;
        }

    }
}