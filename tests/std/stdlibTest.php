<?php

use dqdp\SQL\Condition;
use dqdp\StdObject;
use PHPUnit\Framework\TestCase;

class stdlibTest extends TestCase
{
	function test_sql_select1() {
		$MainCond = search_sql("a b c", ["field1", "field2"]);
		$this->assertTrue($MainCond == "((UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?) AND (UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?) AND (UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?))");
		$this->assertTrue($MainCond->vars() === ["%A%", "%A%", "%B%", "%B%", "%C%", "%C%"]);
	}

	function test_sql_select2() {
		$MainCond = __search_sql("a b c", ["field1", "field2"], function($word, $field, $Cond){
			$Cond->add_condition(["$field LIKE ?", "%".$word."%"], Condition::OR);
		});
		//printr((string)$MainCond, $MainCond->vars());
		$this->assertTrue($MainCond == "((field1 LIKE ? OR field2 LIKE ?) AND (field1 LIKE ? OR field2 LIKE ?) AND (field1 LIKE ? OR field2 LIKE ?))");
		$this->assertTrue($MainCond->vars() === ["%a%", "%a%", "%b%", "%b%", "%c%", "%c%"]);
	}

	function test_split_words1() {
		$res = ["a", "b", "c"];
		$this->assertTrue(split_words("a b c") === $res);
		$this->assertTrue(split_words("a  b  c") === $res);
		$this->assertTrue(split_words(" a b c") === $res);
		$this->assertTrue(split_words("a b c ") === $res);
		$this->assertTrue(split_words(" a b c ") === $res);
		$this->assertTrue(split_words(" a       b c      ") === $res);
	}

	function test_split_words2() {
		$res = ["ā", "b", "č"];
		$this->assertTrue(split_words("ā b č") === $res);
		$this->assertTrue(split_words("ā  b  č") === $res);
		$this->assertTrue(split_words(" ā b č") === $res);
		$this->assertTrue(split_words("ā b č ") === $res);
		$this->assertTrue(split_words(" ā b č ") === $res);
		$this->assertTrue(split_words(" ā       b č      ") === $res);
	}

	function test_floatpoint(){
		$this->assertEquals(floatpoint('0.00'), 0);
		$this->assertEquals(floatpoint('0,00'), 0);
		$this->assertEquals(floatpoint('0,01'), 0.01);
		$this->assertEquals(floatpoint('-0.01'), -0.01);
		$this->assertEquals(floatpoint('-0,02'), -0.02);
	}

	function test_ktolower1(){
		$a = ['k'=>1,'A'=>2];
		$this->assertEquals(ktolower($a), ['k'=>1,'a'=>2]);

		$a = ['k'=>1,'K'=>2];
		$this->assertEquals(ktolower($a), ['k'=>2]);

		$a = new StdObject(['k'=>1,'A'=>2]);
		$this->assertEquals(ktolower($a), new StdObject(['k'=>1,'a'=>2]));

		$a = new StdObject(['k'=>1,'K'=>2]);
		$this->assertEquals(ktolower($a), new StdObject(['k'=>2]));
	}

	function test_compacto1(){
		$a = [];
		$this->assertEquals(compacto($a), []);

		$a = [1];
		$this->assertEquals(compacto($a), [1]);

		$a = [1,2];
		$this->assertEquals(compacto($a), [1,2]);

		$a = [0,1];
		$this->assertEquals(compacto($a), [1=>1]);

		$a = ['k'=>1,'A'=>2];
		$this->assertEquals(compacto($a), ['k'=>1,'A'=>2]);

		$a = ['k'=>0,'A'=>2];
		$this->assertEquals(compacto($a), ['A'=>2]);

		$a = ['k'=>1,'A'=>''];
		$this->assertEquals(compacto($a), ['k'=>1]);

		$a = ['k'=>1,'A'=>[0,0,1]];
		$this->assertEquals(compacto($a), ['k'=>1,'A'=>[2=>1]]);

		$a = ['k'=>1,'A'=>[1]];
		$this->assertEquals(compacto($a), ['k'=>1, 'A'=>[1]]);

		$a = new StdObject(['k'=>1,'A'=>2]);
		$this->assertEquals(compacto($a), new StdObject(['k'=>1,'A'=>2]));

		$a = new StdObject(['k'=>0,'A'=>2]);
		$this->assertEquals(compacto($a), new StdObject(['A'=>2]));

		$a = new StdObject(['k'=>1,'A'=>'']);
		$this->assertEquals(compacto($a), new StdObject(['k'=>1]));

		$a = new StdObject(['k'=>1,'A'=>[0,0,1]]);
		$this->assertEquals(compacto($a), new StdObject(['k'=>1,'A'=>[2=>1]]));
	}

	function test_flatten1(){
		$a = [];
		$this->assertEquals(flatten($a), []);

		$a = [1];
		$this->assertEquals(flatten($a), [1]);

		$a = [1,2];
		$this->assertEquals(flatten($a), [1,2]);

		$a = [0,2];
		$this->assertEquals(flatten($a), [0,2]);

		$a = [0=>[2],3];
		$this->assertEquals(flatten($a), [2,3]);

		$a = [0=>[2],3=>4];
		$this->assertEquals(flatten($a), [2,4]);

		$a = [0=>[2],3=>[4,5]];
		$this->assertEquals(flatten($a), [2,4,5]);

		$a = new StdObject();
		$this->assertEquals(flatten($a), []);

		$a = new StdObject(["a"=>2]);
		$this->assertEquals(flatten($a), [2]);

		$a = new StdObject(["a"=>["b"=>3]]);
		$this->assertEquals(flatten($a), [3]);

		$a = new StdObject(["a"=>["b"=>3,"c"=>4,5]]);
		$this->assertEquals(flatten($a), [3,4,5]);
	}

	function test_getbyk1(){
		$a = ['k'=>1,'A'=>2];
		$this->assertEquals(getbyk($a, 'k'), [1]);

		$a = [['k'=>1],['A'=>2]];
		$this->assertEquals(getbyk($a, 'k'), [1]);

		$a = [['k'=>1],['k'=>2]];
		$this->assertEquals(getbyk($a, 'k'), [1,2]);

		$a = [['k'=>1],['k'=>2],['a'=>['k'=>3]]];
		$this->assertEquals(getbyk($a, 'k'), [1,2,3]);
	}
}
