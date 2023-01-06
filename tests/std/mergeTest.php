<?php

use dqdp\StdObject;
use PHPUnit\Framework\TestCase;

class mergeTest extends TestCase
{
	protected static $db;

	public function test1() {
		$a = ['a'=>1];
		$b = ['b'=>2];
		$c = merge($a, $b);
		$this->assertEquals($c, array_merge($a, $b));
	}

	public function test2() {
		$f = static function($i){
			return $i;
		};

		$a = ['a'=>1, 'f'=>$f];
		$b = ['c'=>2];
		$c = merge($a, $b);
		$this->assertEquals($c, array_merge($a, $b));
	}

	public function test3() {
		$f = static function($i){
			return $i;
		};

		$a = new StdObject(['a'=>1, 'f'=>$f]);
		$b = new StdObject(['c'=>2]);
		$c = merge($a, $b);
		$this->assertEquals($c, new StdObject(['a'=>1, 'f'=>$f, 'c'=>2]));
	}

	public function test4() {
		$a = ['a'=>1, 'b'=>2];
		$b = merge_only(['a'], $a);
		$this->assertEquals($b, ['a'=>1]);
	}

	public function test5() {
		$a = new StdObject(['a'=>1, 'b'=>2]);
		$b = merge_only(['a'], $a);
		$this->assertEquals($b, (object)(['a'=>1]));
	}

	public function test6() {
		$f = static function($i){
			return $i;
		};

		$a = new StdObject(['a'=>1, 'b'=>2, 'f'=>$f]);
		$b = merge_only(['a', 'f'], $a);
		$this->assertEquals($b, (object)(['a'=>1, 'f'=>$f]));
	}

	public function test7() {
		$a = ['a'=>1, 'b'=>2, 'f'=>static function($i){
			return $i;
		}];
		$b = merge(['a'=>2, 'f'=>3], $a);
		$this->assertEquals($b, (['a'=>1, 'f'=>static function($i){
			return $i;
		}, 'b'=>2]));
	}

	public function test8() {
		$a = ['a'=>1, 'b'=>2, 'f'=>static function($i){
			return $i;
		}];
		$b = merge_only(['a', 'f'], $a);
		$this->assertEquals($b, ['a'=>1, 'f'=>static function($i){
			return $i;
		}]);
	}
}
