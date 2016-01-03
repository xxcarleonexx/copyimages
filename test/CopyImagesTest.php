<?php
	
	namespace CopyImagesTest;

	class CopyImagesTest extends PHPUnit_Framework_TestCase 
	{
		function testTrue() 
		{
			$copyimg = new CopyImages();
			$this->assertTrue($copyimg->getTrue());
		}
	}
