<?php

use dqdp\StdObject;
use PHPUnit\Framework\TestCase;

//printr("\n",$a,$b);
class __object_filterTest extends TestCase
{
	protected static $db;

	function f_isset($i){
		return !is_null($i);
	}

	function f_strl3($i){
		return strlen($i)>3;
	}

	# Nodublē objektu, lai pārbaudītu vai f-ija to nav izmainījusi
	public function adup(array $a, $f) {
		$b = $a;
		$c = __object_filter($a, [$this, $f]);
		$this->assertEquals($a, $b);
		return $c;
	}

	public function odup(object $a, $f) {
		$b = clone $a;
		$c = __object_filter($a, [$this, $f]);
		$this->assertEquals($a, $b);
		return $c;
	}

	public function test1() {
		$a = [];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, []);

		$a = [[]];
		$b = $this->adup($a, 'f_isset');
		$c = array_filter($a, [$this, 'f_isset']);
		$this->assertEquals($b, [[]]);

		$a = [[[]]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, [[[]]]);

		$a = [0];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, [0]);

		$a = [[0]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, [[0]]);

		$a = [null];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, []);

		$a = [[null]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, [[]]);

		$a = ['a'=>[null]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, ['a'=>[]]);

		$a = ['a'=>['b'=>[null]]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, ['a'=>['b'=>[]]]);

		$a = ['a'=>['b'=>[1]]];
		$b = $this->adup($a, 'f_isset');
		$this->assertEquals($b, $a);
	}

	public function test2() {
		$a = [];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = [[]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = [[[]]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = [0];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, []);

		$a = [[0]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, [[]]);

		$a = [null];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, []);

		$a = [[null]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, [[]]);

		$a = ['a'=>[null]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, ['a'=>[]]);

		$a = ['a'=>['b'=>[null]]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, ['a'=>['b'=>[]]]);

		$a = ['a'=>['b'=>[1]]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, ['a'=>['b'=>[]]]);
	}

	public function test3() {
		$a = ['test'];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, ['test']);

		$a = [['test']];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = [[['test']]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = ['test'=>0];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, []);

		$a = [[0],'test'];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, [[],'test']);

		$a = [[null],[],['tes'],['testk'=>'test']];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, [[],[],[],['testk'=>'test']]);

		$a = ['a'=>[null],'b'=>'test'];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, ['a'=>[],'b'=>'test']);

		$a = ['a'=>['b'=>['test']]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);

		$a = ['a'=>['b'=>[1213123]]];
		$b = $this->adup($a, 'f_strl3');
		$this->assertEquals($b, $a);
	}
}
