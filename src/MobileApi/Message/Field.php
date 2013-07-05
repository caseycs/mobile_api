<?php
namespace MobileApi\Message;

abstract class Field
{
    const OPTIONAL = 'optional';
    const REQUIRED = 'required';
    const REPEATED = 'repeated';

    const BOOLEAN = 'boolean';
    const STRING = 'string';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const ASSOC = 'assoc';
    const ENUM = 'enum';
}
