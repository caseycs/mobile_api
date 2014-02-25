<?php
namespace MobileApi;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use MobileApi\Message\Request\RequestInterface;

interface ControllerInterface
{
    function run(RequestInterface $Request, SessionInterface $Session);
}
