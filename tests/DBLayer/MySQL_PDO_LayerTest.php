<?php

use dqdp\DBLayer\MySQL_PDO_Layer;
use PHPUnit\Framework\TestCase;

class MySQL_PDO_LayerTest extends TestCase
{
	protected static $db;

	public static function setUpBeforeClass(): void {
		self::$db = new MySQL_PDO_Layer;
		self::$db->connect('localhost', 'root', '', 'dblayer_test');
	}

	// protected function setUp(): void {
	// }

	// protected function assertPreConditions(): void
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");
	// }

	public function testSelect1() {
		$data = self::$db->execute("SELECT * FROM table1");
		$this->assertTrue($data !== false);
	}

	public function testSelect2() {
		$data = self::$db->execute("SELECT * FROM table1 WHERE name = 'test'");
		$this->assertTrue($data !== false);
	}

	public function testSelect3() {
		$data = self::$db->execute("SELECT * FROM table1 WHERE name = ?", ['test']);
		$this->assertTrue($data !== false);
	}

	public function testSelect4() {
		$q = self::$db->prepare("SELECT * FROM table1 WHERE name = ?");
		$data = self::$db->execute($q, ['test']);
		$this->assertTrue($data !== false);
	}

	public function testInsert1() {
		$data = self::$db->execute("INSERT INTO table1 (name) VALUES ('test1')");
		$this->assertTrue($data !== false);
	}

	public function testInsert2() {
		$data = self::$db->execute("INSERT INTO table1 (name) VALUES (?)", ['test2']);
		$this->assertTrue($data !== false);
	}

	public function testInsert3() {
		$q = self::$db->prepare("INSERT INTO table1 (name) VALUES (?)");
		$data = self::$db->execute($q, ['test3']);
		$this->assertTrue($data !== false);
	}

	public static function tearDownAfterClass(): void {
		self::$db->close();
	}

	// protected function assertPostConditions(): void
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");
	// }

	// protected function tearDown(): void {
	// 	fwrite(STDOUT, __METHOD__ . "\n");
	// }

	// protected function onNotSuccessfulTest(Throwable $t): void
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");
	// 	throw $t;
	// }
}
