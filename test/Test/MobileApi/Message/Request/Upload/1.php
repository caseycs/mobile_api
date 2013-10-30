<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 16.10.13
 * Time: 19:07
 * To change this template use File | Settings | File Templates.
 */

namespace Test\MobileApi\Message\Request;


use MobileApi\Message\Request\RequestInterface;
use MobileApi\Message\Request\UploadInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload_1 implements RequestInterface, UploadInterface {

    public $name;

    protected $file;

    function getStructure()
    {
        return array(
            'name' => array(\MobileApi\Message\Field::REQUIRED, \MobileApi\Message\Field::STRING),
        );
    }

    function getAvailableResponses()
    {
        return array(
            'Upload_1',
        );
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
}
