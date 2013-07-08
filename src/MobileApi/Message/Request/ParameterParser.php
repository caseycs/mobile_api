<?php
namespace MobileApi\Message\Request;

use MobileApi\Message\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterParser
{
    public function toArray(RequestInterface $Request, ParameterBag $ParameterBag)
    {
        $structure = $Request->getStructure();
        return $this->fillInternal($structure, $ParameterBag->all());
    }

    private function fillInternal(array $structure, array $data)
    {
        foreach ($data as $key => &$value) {
            if (isset($structure[$key])) {
                switch ($structure[$key][1]) {
                    case Field::INTEGER:
                        if ((string)(int)$data[$key] === $data[$key]) {
                            $value = (int)$data[$key];
                        }
                        break;
                    case Field::FLOAT:
                        if ((string)(float)$data[$key] === $data[$key]) {
                            $value = (float)$data[$key];
                        }
                        break;
                    case Field::BOOLEAN:
                        if ($data[$key] === 'true') {
                            $value = true;
                        } elseif ($data[$key] === 'false') {
                            $value = false;
                        }
                        break;
                    case Field::ASSOC:
                        if ($structure[$key][0] === Field::REPEATED) {
                            foreach ($value as &$v) {
                                if (is_array($v)) {
                                    $v = $this->fillInternal($structure[$key][2], $v);
                                }
                            }
                        } else {
                            if (is_array($value)) {
                                $value = $this->fillInternal($structure[$key][2], $value);
                            }
                        }
                        break;
                }
            }
        }
        return $data;
    }
}
