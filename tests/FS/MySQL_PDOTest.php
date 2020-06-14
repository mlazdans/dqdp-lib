<?php

use dqdp\DBA\MySQL_PDO;

class MySQL_PDOTest extends FSTest
{
	public static function setUpBeforeClass(): void {
		self::$db = new MySQL_PDO;
		self::$db->connect('localhost', 'root', '', 'dblayer_test');
	}
}
