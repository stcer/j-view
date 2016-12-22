<?php

namespace j\view;

use ReflectionMethod;

/**
 * Class BaseTest
 * @package j\view
 */
class BaseTest extends \PHPUnit_Framework_TestCase{
	protected function setUp(){

	}

	public function testGetTpl(){
		$view = new Base();
		$dir = __DIR__ . '/template/';
		$view->setDir($dir);

		$method = new ReflectionMethod(View::class, 'getFile');
		$method->setAccessible(true);
		$file = $method->invoke($view, 'test.php');
		$this->assertEquals($dir . 'test.php', $file);
	}

	public function testExist(){
		$view = new Base();
		$view->setDir(__DIR__ . '/template');
		$this->assertEquals(true, $view->exist('test.php'));
	}

	public function testSetContent(){
        $view = new View();
        $view->setDir(__DIR__ . '/template');

        $content = 'main body';
        $view->setBlockContent('main', $content);
        $result = $view->render("base.php");
        $true = strpos($result, $content) > 0;
        $this->assertEquals(true, $true);
    }

	protected function tearDown() {

	}
}