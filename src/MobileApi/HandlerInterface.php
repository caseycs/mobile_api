<?php
namespace MobileApi;


use MobileApi\Message\Request\RequestInterface;
use MobileApi\Message\Response\ResponseInterface;

interface HandlerInterface {

    /**
     * @param RequestInterface $Request
     * @return null|ResponseInterface
     */
    public function run(RequestInterface $Request);

}