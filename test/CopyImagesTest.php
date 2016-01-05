<?php
    
    namespace CopyImages;
    
    use PHPUnit_Framework_TestCase;
    
    class CopyImagesTest extends PHPUnit_Framework_TestCase 
    {
        
        public function testCreateObj()
        {
            $path = "www.ya.ru";
            $copyimg = new CopyImages($path);
            $this->assertEquals($path, $copyimg->getAddress());
        }
        
        /**
        * @expectedException Exception
        * @expectedExceptionMessage Please set host address
        */
        public function testExceptionHasRightMessage()
        {
            $copyimg = new CopyImages("");
        }
        
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
    }