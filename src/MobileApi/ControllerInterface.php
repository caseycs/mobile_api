<?php
namespace MobileApi;

use MobileApi\Message\Request\RequestInterface;

interface ControllerInterface
{
    function run(RequestInterface $Request);
}
