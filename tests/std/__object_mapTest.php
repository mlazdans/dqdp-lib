<?php declare(strict_types = 1);

use dqdp\StdObject;
use PHPUnit\Framework\TestCase;

class __object_mapTest extends TestCase
{
	protected static $db;

	public function test_array() {
		$f = function($i){
			return $i;
		};

		$datas = [
			[[]],
			[1],

			[[1]],
			['a'],

			[['a']],
			['a'=>1],

			[['a'=>'b']],
			['k'=>['a'=>'b']],
		];

		foreach($datas as $i=>$a){
			$capture_before = (array)$a;
			$b = __object_map($a, $f);
			$capture_after = (array)$a;

			$this->assertEquals($capture_before, $capture_after);
			$this->assertEquals($b, $a);
		}

		$DO = (array)($datas);
		$b = __object_map($DO, $f);
		$this->assertEquals($DO, $b);
	}

	public function test_obj() {
		$f = function($i){
			return $i;
		};

		$datas = [
			(object)[[]],
			new StdObject([[]]),

			(object)[1],
			new StdObject([1]),

			(object)[[1]],
			new StdObject([[1]]),

			(object)['a'],
			new StdObject(['a']),

			(object)[['a']],
			new StdObject([['a']]),

			(object)['a'=>1],
			new StdObject(['a'=>1]),

			(object)[['a'=>'b']],
			new StdObject([['a'=>'b']]),

			(object)['k'=>['a'=>'b']],
			new StdObject(['k'=>['a'=>'b']]),
		];

		foreach($datas as $i=>$a){
			$capture_before = (array)$a;
			$b = __object_map($a, $f);
			$capture_after = (array)$a;

			$this->assertEquals($capture_before, $capture_after);
			$this->assertEquals($b, (object)(array)$a);
		}

		// $DO = (object)($datas);
		// $b = __object_map($DO, $f);
		// $this->assertEquals((object)(array)$DO, $b);
	}

	public function test_array_modification() {
		$f = function(int $i): int {
			return $i * 2;
		};

		$datas = [
			[],
			[0],
			[0,0,0],
			[1,2,3],
			["a"=>1,"b"=>2,"c"=>3],
		];
		$datas[] = [
			"more_nesting"=>$datas[count($datas) - 1]
		];

		$results = [
			[],
			[0],
			[0,0,0],
			[2,4,6],
			["a"=>2,"b"=>4,"c"=>6],
		];
		$results[] = [
			"more_nesting"=>$results[count($results) - 1]
		];

		foreach($datas as $i=>$a){
			$b = __object_map($a, $f);
			$this->assertEquals($b, $results[$i]);
		}
	}

	public function test_obj_modification() {
		$f = function(int $i): int {
			return $i * 2;
		};

		$datas = [
			(object)[],
			(object)[0],
			(object)[0,0,0],
			(object)[1,2,3],
			(object)["a"=>1,"b"=>2,"c"=>3],
		];
		$datas[] = [
			"more_nesting"=>$datas[count($datas) - 1]
		];

		$results = [
			(object)[],
			(object)[0],
			(object)[0,0,0],
			(object)[2,4,6],
			(object)["a"=>2,"b"=>4,"c"=>6],
		];
		$results[] = [
			"more_nesting"=>$results[count($results) - 1]
		];

		foreach($datas as $i=>$a){
			$b = __object_map($a, $f);
			$this->assertEquals($b, $results[$i]);
		}
	}
}
