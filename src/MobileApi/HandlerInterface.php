<?php
namespace MobileApi;

use MobileApi\Message\Request\RequestInterface;
use MobileApi\Message\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface HandlerInterface {

    /**
     * @param ControllerInterface $Controller
     * @param RequestInterface $Request
     * @return null|ResponseInterface
     */
    public function run(ControllerInterface $Controller, RequestInterface $Request, SessionInterface $Session);

}
