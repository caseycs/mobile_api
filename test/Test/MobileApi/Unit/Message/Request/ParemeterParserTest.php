<?php
namespace Test\MobileApi\Unit\Message\Request;

use MobileApi\Message\Request\ParameterParser;
use MobileApi\Message\Field;

class ParemeterParserTest extends \PHPUnit_Framework_TestCase
{
    public function provider_toArray()
    {
        return array(
            array(
                array('a' => array(Field::REQUIRED, Field::STRING)),
                array(
                    'a' => '5',
                    'b' => '15',
                ),
                array(
                    'a' => '5',
                    'b' => '15',
                ),
                'orphan',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::STRING)),
                array(
                    'a' => '5',
                ),
                array(
                    'a' => '5',
                ),
                'string',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::INTEGER)),
                array(
                    'a' => '5',
                ),
                array(
                    'a' => 5,
                ),
                'integer',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::FLOAT)),
                array(
                    'a' => '5.5',
                ),
                array(
                    'a' => 5.5,
                ),
                'float',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::FLOAT)),
                array(
                    'a' => '5',
                ),
                array(
                    'a' => (float)5,
                ),
                'float as int',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::BOOLEAN)),
                array(
                    'a' => 'true',
                ),
                array(
                    'a' => true,
                ),
                'true',
            ),
            array(
                array('a' => array(Field::REQUIRED, Field::BOOLEAN)),
                array(
                    'a' => 'false',
                ),
                array(
                    'a' => false,
                ),
                'false',
            ),
            array(
                array(
                    'a' => array(
                        Field::REQUIRED,
                        Field::ASSOC,
                        array(
                            'b' => array(Field::REQUIRED, Field::INTEGER),
                            'c' => array(Field::REQUIRED, Field::FLOAT),
                        )
                    ),
                ),
                array(
                    'a' => array('b' => '10', 'c' => '5.5'),
                ),
                array(
                    'a' => array('b' => 10, 'c' => 5.5),
                ),
                'assoc',
            ),
            array(
                array(
                    'a' => array(
                        Field::REPEATED,
                        Field::ASSOC,
                        array(
                            'b' => array(Field::REQUIRED, Field::INTEGER),
                            'c' => array(Field::REQUIRED, Field::BOOLEAN),
                        )
                    ),
                ),
                array(
                    'a' => array(array('b' => '10','c' => 'true'), array('b' => '20', 'c' => 'false')),
                ),
                array(
                    'a' => array(array('b' => 10, 'c' => true), array('b' => 20, 'c' => false)),
                ),
                'repeated assoc',
            ),
        );
    }

    /**
     * @dataProvider provider_toArray
     */
    public function test_toArray(array $structure, array $parameters, $expected, $comment)
    {
        $vars = '';
        foreach ($structure as $k => $v) {
            $vars .= "public \${$k};";
        }

        $classname = 'tmp_' . uniqid();
        $eval = 'class ' . $classname . ' implements \MobileApi\Message\Request\RequestInterface {
            ' . $vars . '
            public function getStructure(){return ' . var_export($structure, true) . ';}
            public function getAvailableResponses(){}
        }';
        eval($eval);
        $Request = new $classname;

        $ParameterParser = new ParameterParser;
        $ParameterBag = new \Symfony\Component\HttpFoundation\ParameterBag($parameters);
        $this->assertSame($expected, $ParameterParser->toArray($Request, $ParameterBag), $comment);
    }
}
