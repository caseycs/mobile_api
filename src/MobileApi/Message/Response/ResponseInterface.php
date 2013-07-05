<?php
namespace MobileApi\Message\Response;

use MobileApi\Message\MessageInterface;

interface ResponseInterface extends MessageInterface
{
    function getName();
}
