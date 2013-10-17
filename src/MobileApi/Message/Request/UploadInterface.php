<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SLozhkin
 * Date: 16.10.13
 * Time: 19:27
 * To change this template use File | Settings | File Templates.
 */

namespace MobileApi\Message\Request;


use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadInterface
 * @package MobileApi\Message\Request
 */
interface UploadInterface {

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file);

    /**
     * @return UploadedFile
     */
    public function getFile();

}