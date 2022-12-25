<?php

use dqdp\DBA\driver\MySQL_PDO;
use dqdp\Settings;
use dqdp\SQL\SQL;
use PHPUnit\Framework\TestCase;

# TODO: atsevišķi DBA
class SettingsTest extends TestCase
{
	protected static $db;

	public static function setUpBeforeClass(): void {
		SQL::$lex = 'mysql';
		self::$db = new MySQL_PDO;
		self::$db->connect('localhost', 'root', '', 'dqdp_tests');
	}

	// protected function setUp(): void {
	// }

	// protected function assertPreConditions(): void
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");
	// }

	public function testSelect1() {
		$settings = (new Settings('test'))->set_trans(self::$db);
		$settings->set_struct([
			'TEST'=>'int',
		]);

		$settings->set('TEST', 22);
		$settings->save([]);
		//print_r($settings);
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
