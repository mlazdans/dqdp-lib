<?php

use dqdp\DBA\DBA;
use PHPUnit\Framework\TestCase;

abstract class DBATest extends TestCase
{
	protected static DBA $db;

	public function testSelect1() {
		$this->assertTrue(self::$db->execute("SELECT * FROM table1") !== false);
	}

	public function testSelect2() {
		$this->assertTrue(self::$db->execute("SELECT * FROM table1 WHERE name = 'test'") !== false);
	}

	public function testSelect3() {
		$this->assertTrue(self::$db->execute("SELECT * FROM table1 WHERE name = ?", 'test') !== false);
	}

	public function testSelect4() {
		$q = self::$db->prepare("SELECT * FROM table1 WHERE name = ?");
		$this->assertTrue(self::$db->execute_prepared($q, 'test') !== false);
	}

	public function testSelect5() {
		$sql = sprintf("SELECT * FROM table1 WHERE name = '%s'", self::$db->escape("'test"));
		$this->assertTrue(self::$db->execute($sql) !== false);
	}

	public function testInsert1() {
		$this->assertTrue(self::$db->execute("INSERT INTO table1 (name) VALUES ('test1')") !== false);
	}

	public function testInsert2() {
		$this->assertTrue(self::$db->execute("INSERT INTO table1 (name) VALUES (?)", 'test2') !== false);
	}

	public function testInsert3() {
		$q = self::$db->prepare("INSERT INTO table1 (name) VALUES (?)");
		$this->assertTrue(self::$db->execute_prepared($q, 'test3') !== false);
	}

	public static function tearDownAfterClass(): void {
		self::$db->close();
	}

}
