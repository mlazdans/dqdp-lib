<?php

use PHPUnit\Framework\TestCase;

final class EoTest extends TestCase
{
	function testEmptyStd(){
		$this->assertTrue(empty($DATA));
		$this->assertTrue(empty($DATA->KEY_TEST));
	}

	function testEmptyEo(){
		$DATA = eo();
		$this->assertFalse(empty($DATA));
		$this->assertTrue(is_empty($DATA));
		$this->assertTrue(empty($DATA->KEY_TEST));
		$this->assertTrue(!isset($DATA->KEY_TEST));
	}

	function testNonEmptyEo(){
		$DATA = eo();
		$DATA->N = null;

		$this->assertFalse(isset($DATA->N));
		$this->assertTrue($DATA->isset('N'));
	}

	function testNull1(){
		$a = 20;
		$this->assertTrue(isset($a));
		$this->assertTrue(is_null($a) === false);
	}

	function testNull2(){
		$a = [];
		$this->assertTrue(isset($a));
		$this->assertTrue(is_null($a) === false);
	}

	function testNull3(){
		$a = [null];
		$this->assertTrue(isset($a[0]) === false);
		$this->assertTrue(array_key_exists(0, $a) === true);
	}

	function testNull4(){
		$this->assertTrue(isset($a) === false);
		$this->assertTrue(isset($a[0]) === false);
		$this->assertTrue(isset($a['test']) === false);
	}
}
