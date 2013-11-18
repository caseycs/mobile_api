<?php
namespace Test\MobileApi\Unit\Message;

use MobileApi\Message\Manager;
use MobileApi\Message\Field;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function provider_isValid_structure()
    {
        return array(
            array(
                array(
                ),
                array(
                    'a' => ''
                ),
                false,
                'orphan key',
            ),
            array(
                array(
                    'version' => array(Field::REQUIRED, Field::INTEGER),
                ),
                array(
                    'varsion' => 5,
                ),
                false,
                'orphan key 1',
            ),
            array(
                array(
                    'a' => array(Field::REQUIRED, Field::INTEGER),
                ),
                array(
                ),
                false,
                'required field not found',
            ),
            array(
                array(
                    'a' => array(Field::REQUIRED, Field::INTEGER),
                ),
                array(
                    'a' => 1,
                ),
                true,
                'required field ok',
            ),
            array(
                array(
                    'a' => array(Field::OPTIONAL, Field::INTEGER),
                ),
                array(
                ),
                true,
                'optional not presented',
            ),
            array(
                array(
                    'a' => array(Field::OPTIONAL, Field::INTEGER),
                ),
                array(
                    'a' => 5,
                ),
                true,
                'optional presented',
            ),
            array(
                array(
                    'a' => array(Field::REPEATED, Field::INTEGER),
                ),
                array(
                    'a' => array(5,6,7),
                ),
                true,
                'repeated scalar ok',
            ),
            array(
                array(
                    'a' => array(Field::REPEATED, Field::INTEGER),
                ),
                array(
                    'a' => '10',
                ),
                false,
                'repeated scalar fail',
            ),
            array(
                array(
                    'a' => array(Field::REQUIRED, Field::ASSOC, array(
                        'a' => array(Field::REQUIRED, Field::INTEGER),
                        'b' => array(Field::REQUIRED, Field::STRING),
                    )),
                ),
                array(
                    'a' => array('a' => 10, 'b' => '20'),
                ),
                true,
                'required assoc ok',
            ),
            array(
                array(
                    'a' => array(Field::REQUIRED, Field::ASSOC, array(
                        'a' => array(Field::REQUIRED, Field::INTEGER),
                        'b' => array(Field::OPTIONAL, Field::STRING),
                    )),
                ),
                array(
                    'a' => array('a' => 10, 'b' => null),
                ),
                false,
                'required assoc fail',
            ),
            array(
                array(
                    'a' => array(
                        Field::REPEATED,
                        Field::ASSOC,
                        array(
                            'a' => array(Field::REQUIRED, Field::INTEGER),
                        ),
                    ),
                ),
                array(
                    'a' => array(array('a' => 10), array('a' => 30)),
                ),
                true,
                'repeated assoc ok',
            ),
            array(
                array(
                    'a' => array(
                        Field::REPEATED,
                        Field::ASSOC,
                        array(
                            'a' => array(Field::REQUIRED, Field::INTEGER),
                        ),
                    ),
                ),
                array(
                    'a' => array(array('a' => '10'), array('a' => 30)),
                ),
                false,
                'repeated assoc fail',
            ),
            array(
                array(
                    'a' => array(
                        Field::REPEATED,
                        Field::ASSOC,
                        array(
                            'b' => array(
                                Field::REQUIRED,
                                Field::ASSOC,
                                array(
                                    'c' => array(Field::REQUIRED, Field::INTEGER),
                                )
                            ),
                        ),
                    ),
                ),
                array(
                    'a' => array(array('b' => array('c' => 5))),
                ),
                true,
                'inherited required assoc ok',
            ),
            array(
                array(
                    'a' => array(
                        Field::REPEATED,
                        Field::ASSOC,
                        array(
                            'b' => array(
                                Field::REPEATED,
                                Field::ASSOC,
                                array(
                                    'c' => array(Field::REQUIRED, Field::INTEGER),
                                )
                            ),
                        ),
                    ),
                ),
                array(
                    'a' => array(array('b' => array(array('c' => 5)))),
                ),
                true,
                'inherited repeated assoc ok',
            ),
        );
    }

    /**
     * @dataProvider provider_isValid_structure
     */
    public function test_isValid_structure(array $structure, array $object_params, $expected, $comment)
    {
        $vars = '';
        foreach ($object_params as $k => $v) {
            $vars .= "public \$$k = " . var_export($v, true) . ';';
        }

        $classname = 'tmp_' . uniqid();
        $eval = 'class ' . $classname . ' implements \MobileApi\Message\MessageInterface {
            ' . $vars . '
            function getStructure(){return ' . var_export($structure, true) . ';}
        }';
        eval($eval);
        $Message = new $classname;

        $Manager = new Manager;
        $error = '';
        $result = $Manager->isValid($Message, $error);
        $this->assertSame($expected, $result, $comment . ' ' . $error);
    }

    public function provider_isValid_type()
    {
        return array(
            array(array(Field::REQUIRED, Field::INTEGER), 5, true, 'integer ok'),
            array(array(Field::REQUIRED, Field::INTEGER), '5', false, 'integer fail'),
            array(array(Field::REQUIRED, Field::STRING), '5', true, 'string ok'),
            array(array(Field::REQUIRED, Field::STRING), 5, false, 'string fail'),
            array(array(Field::REQUIRED, Field::BOOLEAN), true, true, 'boolean ok'),
            array(array(Field::REQUIRED, Field::BOOLEAN), false, true, 'boolean ok'),
            array(array(Field::REQUIRED, Field::BOOLEAN), 5, false, 'boolean fail'),
            array(array(Field::REQUIRED, Field::FLOAT), 5.5, true, 'float ok'),
            array(array(Field::REQUIRED, Field::FLOAT), 5, false, 'float fail'),
            array(array(Field::REQUIRED, Field::ENUM, array(1,2,3)), 1, true, 'enum ok'),
            array(array(Field::REQUIRED, Field::ENUM, array(1,2,3)), '3', false, 'enum fail'),
            array(array(Field::REQUIRED, Field::ENUM, array(1,2,3)), 5, false, 'enum fail'),
        );
    }

    /**
     * @dataProvider provider_isValid_type
     */
    public function test_isValid_type($type, $value, $expected, $comment)
    {
        $classname = 'tmp_' . uniqid();
        $eval = 'class ' . $classname . ' implements \MobileApi\Message\MessageInterface {
            public $test = ' . var_export($value, true) . ';
            function getStructure(){return array("test" => ' . var_export($type, true) . ');}
        }';
        eval($eval);
        $Message = new $classname;

        $Manager = new Manager;
        $error = '';
        $result = $Manager->isValid($Message, $error);
        $this->assertSame($expected, $result, $comment . ' / ' . $error);
    }
}
