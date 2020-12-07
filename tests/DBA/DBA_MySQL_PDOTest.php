<?php

use dqdp\DBA\driver\MySQL_PDO;
use dqdp\SQL;

class DBA_MySQL_PDOTest extends DBATest
{
	public static function setUpBeforeClass(): void {
		SQL::$lex = 'mysql';
		self::$db = new MySQL_PDO;
		self::$db->connect('localhost', 'root', '', 'dqdp_tests');
	}
}
