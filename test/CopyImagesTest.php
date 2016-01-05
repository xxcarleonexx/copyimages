<?php
/**
* CopyImagesTest служит тестом для CopyImages.
*
* @author Sergey Rusanov
* @version 0.0.1-dev
*/
namespace CopyImages;

class CopyImagesTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Тестируем, что директории нет. Или если она есть, то ее удаляем.
    */
    public function setUp()
    {
        $dirName = "downloaded";
        if(is_dir($dirName)) {
            $dir = dir($dirName);
            while(false !== ($entry = $dir->read())) {
                if ($entry === "." || $entry === "..") continue;
                if (file_exists($dirName . DIRECTORY_SEPARATOR . $entry)) {
                    @unlink($dirName . DIRECTORY_SEPARATOR . $entry);
                }
            }
            $dir->close();
            rmdir($dirName);
        }
    }

    /**
    * Тестируем успешность создания объекта.
    */
    public function testCreateObj()
    {
        $path = "www.ya.ru";
        $copyimg = new CopyImages($path);
        $this->assertEquals($path, $copyimg->getAddress());
    }

    /**
    * Тестируем правильность возникновения исключения.
    * @expectedException Exception
    * @expectedExceptionMessage Please set host address
    */
    public function testExceptionHasRightMessage()
    {
        $copyimg = new CopyImages("");
    }

    /**
    * Тестируем правильность нахождения изображений на хосте.
    */
    public function testParseFromUrl()
    {
        $path = "http://www.w3.org/";
        $expected = [
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/wplogo_transparent.png",
            "/2015/04/w3cx-home.png",
            "/2008/site/images/w3devcampus.png",
            "/2008/site/images/ttwf-dinos.png",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2008/site/images/header-link.gif",
            "/2014/10/stdvidthumb.png"
        ];
        $copyimg = new CopyImages($path);
        $this->assertEquals($expected, $copyimg->parseFromUrl());
    }

    /**
    * Тестируем какие именно файлы скопированы.
    */
    public function testCopyFiles()
    {
        $path = "http://www.w3.org/";
        $expected = [
            ".",
            "..",
            "header-link.gif",
            "stdvidthumb.png",
            "ttwf-dinos.png",
            "w3cx-home.png",
            "w3devcampus.png",
            "wplogo_transparent.png"
        ];
        $copyimg = new CopyImages($path);
        $copyimg->copyFiles();
        $this->assertEquals($expected, scandir("downloaded"));
    }

    /**
    * Очищаем после работы тестировщика.
    */
     public function tearDown()
    {
        $dirName = "downloaded";
        if(is_dir($dirName)) {
            $dir = dir($dirName);
            while(false !== ($entry = $dir->read())) {
                if ($entry === "." || $entry === "..") continue;
                if (file_exists($dirName . DIRECTORY_SEPARATOR . $entry)) {
                    @unlink($dirName . DIRECTORY_SEPARATOR . $entry);
                }
            }
            $dir->close();
            rmdir($dirName);
        }
    }
}
