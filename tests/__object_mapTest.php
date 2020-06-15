<?php

use dqdp\StdObject;
use PHPUnit\Framework\TestCase;

class __object_mapTest extends TestCase
{
	protected static $db;

	public function test1() {
		$f = function($i){
			return $i;
		};

		$datas = [
			[[]],
			(object)[[]],
			new StdObject([[]]),

			[1],
			(object)[1],
			new StdObject([1]),

			[[1]],
			(object)[[1]],
			new StdObject([[1]]),

			['a'],
			(object)['a'],
			new StdObject(['a']),

			[['a']],
			(object)[['a']],
			new StdObject([['a']]),

			['a'=>1],
			(object)['a'=>1],
			new StdObject(['a'=>1]),

			[['a'=>'b']],
			(object)[['a'=>'b']],
			new StdObject([['a'=>'b']]),

			['k'=>['a'=>'b']],
			(object)['k'=>['a'=>'b']],
			new StdObject(['k'=>['a'=>'b']]),
		];

		foreach($datas as $i=>$a){
			$b = __object_map($a, $f);
			$this->assertEquals($a, $datas[$i]);
			$this->assertEquals($a, $b);
		}

		$DO = new StdObject($datas);
		$b = __object_map($DO, $f);
		$this->assertEquals($DO, $DO);
		$this->assertEquals($DO, $b);
	}

	public function test2() {
		$f = function($i){
			return strtolower($i);
		};

		$a = [
			['k'=>['a'=>'b']],
			(object)['k'=>['a'=>'b']],
			new StdObject(['k'=>['a'=>'b']])
		];

		$b = __object_map($a, $f);
		$this->assertEquals($a, $b);

		$a = [
			['K'=>['A'=>'B']],
			(object)['K'=>['A'=>'B']],
			new StdObject(['K'=>['a'=>'b']])
		];

		$r = [
			['K'=>['A'=>'b']],
			(object)['K'=>['A'=>'b']],
			new StdObject(['K'=>['a'=>'b']])
		];

		$b = __object_map($a, $f);
		$this->assertEquals($b, $r);

		$DO = new StdObject($a);
		$DOR = new StdObject($r);
		$b = __object_map($a, $f);
		$this->assertEquals($DO, new StdObject($a));
		$this->assertEquals(new StdObject($b), $DOR);
	}
}
