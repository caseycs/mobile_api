<?php
namespace MobileApi\Message\Request;

use MobileApi\Message\MessageInterface;

interface RequestInterface extends MessageInterface
{
    function getAvaliableResponses();
}
