<?php
namespace MobileApi\Message;

use MobileApi\Message\Field;

class Manager
{
    public function isValid(\MobileApi\Message\MessageInterface $Message, &$error)
    {
        $structure = $Message->getStructure();

        $message = array();
        foreach (get_object_vars($Message) as $field => $value) {
            if ($value === null) continue;
            $message[$field] = $value;
        }

        return $this->isValidInternal($message, $structure, $error);
    }

    private function isValidInternal(array $message, array $structure, &$error)
    {
        //check orphan fields
        $orphan = array_diff(array_keys($message), array_keys($structure));
        if (!empty($orphan)) {
            $error = 'orphan keys: ' . join(', ', $orphan);
            return false;
        }

        foreach ($structure as $field => $flags) {
            //check existance
            if (!$this->checkPresence($field, $flags[0], $message, $error)) {
                return false;
            }

            //skip unexisted optional field value check
            if ($flags[0] === Field::OPTIONAL && !array_key_exists($field, $message)) {
                continue;
            }

            //check value
            if (!$this->checkValue($structure[$field], $field, $message[$field], $error)) {
                return false;
            }
        }

        return true;
    }

    private function checkPresence($field, $presence_flag, $message, &$error)
    {
        switch ($presence_flag) {
            case Field::OPTIONAL:
                break;
            case Field::REQUIRED:
                if (!array_key_exists($field, $message)) {
                    $error = 'required field not found: ' . $field;
                    return false;
                }
                break;
            case Field::REPEATED:
                if (!array_key_exists($field, $message)) {
                    $error = 'repeated field not found: ' . $field;
                    return false;
                }
                break;
            default:
                $error = 'requirement flag invalid';
                return false;
        }
        return true;
    }

    private function checkValue(array $flags, $field, $value, &$error)
    {
        $value_ok = true;
        if ($flags[0] === Field::REPEATED) {
            if (is_array($value)) {
                if ($flags[1] === Field::ASSOC) {
                    foreach ($value as $v) {
                        if (!is_array($v)) {
                            $value_ok = false;
                            break;
                        }
                        if (!$this->isValidInternal($v, $flags[2], $error)) {
                            $value_ok = false;
                            break;
                        }
                    }
                } else {
                    //scalar
                    $value_ok = true;
                    foreach ($value as $v) {
                        if (!$this->checkScalarValue($flags, $v)) {
                            $value_ok = false;
                            break;
                        }
                    }
                }
            } else {
                $value_ok = false;
            }
        } else {
            if ($flags[1] === Field::ASSOC) {
                if (!$this->isValidInternal($value, $flags[2], $error)) {
                    $value_ok = false;
                }
            } else {
                //scalar
                $value_ok = $this->checkScalarValue($flags, $value);
            }
        }

        if (!$value_ok) {
            if (!empty($error)) {
                $error .= ' / ';
            }
            $error .= 'invalid field value: ' . $field . ' = ' . var_export($value, true);
            return false;
        } else {
            return true;
        }
    }

    private function checkScalarValue(array $flags, $value)
    {
        switch ($flags[1]) {
            case Field::BOOLEAN:
                return is_bool($value);
            case Field::INTEGER:
                return is_int($value);
            case Field::FLOAT:
                return is_float($value);
            case Field::STRING:
                return is_string($value);
                break;
            case Field::ENUM:
                return in_array($value, $flags[2], true);
            default:
                return false;
        }
    }
}
