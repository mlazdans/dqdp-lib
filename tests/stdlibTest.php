<?php

use dqdp\SQL\Condition;
use PHPUnit\Framework\TestCase;

class stdlibTest extends TestCase
{
	public function test_sql_select1() {
		$MainCond = search_sql("a b c", ["field1", "field2"]);
		$this->assertTrue($MainCond == "((UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?) AND (UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?) AND (UPPER(field1) LIKE ? OR UPPER(field2) LIKE ?))");
		$this->assertTrue($MainCond->vars() === ["%A%", "%A%", "%B%", "%B%", "%C%", "%C%"]);
	}

	public function test_sql_select2() {
		$MainCond = __search_sql("a b c", ["field1", "field2"], function($word, $field, $Cond){
			$Cond->add_condition(["$field LIKE ?", "%".$word."%"], Condition::OR);
		});
		//printr((string)$MainCond, $MainCond->vars());
		$this->assertTrue($MainCond == "((field1 LIKE ? OR field2 LIKE ?) AND (field1 LIKE ? OR field2 LIKE ?) AND (field1 LIKE ? OR field2 LIKE ?))");
		$this->assertTrue($MainCond->vars() === ["%a%", "%a%", "%b%", "%b%", "%c%", "%c%"]);
	}

	public function test_split_words1() {
		$res = ["a", "b", "c"];
		$this->assertTrue(split_words("a b c") === $res);
		$this->assertTrue(split_words("a  b  c") === $res);
		$this->assertTrue(split_words(" a b c") === $res);
		$this->assertTrue(split_words("a b c ") === $res);
		$this->assertTrue(split_words(" a b c ") === $res);
		$this->assertTrue(split_words(" a       b c      ") === $res);
	}

	public function test_split_words2() {
		$res = ["ā", "b", "č"];
		$this->assertTrue(split_words("ā b č") === $res);
		$this->assertTrue(split_words("ā  b  č") === $res);
		$this->assertTrue(split_words(" ā b č") === $res);
		$this->assertTrue(split_words("ā b č ") === $res);
		$this->assertTrue(split_words(" ā b č ") === $res);
		$this->assertTrue(split_words(" ā       b č      ") === $res);
	}
}
