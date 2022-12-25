<?php

use dqdp\DBA\driver\IBase;
use dqdp\SQL\SQL;

class FS_IBaseTest extends FSTest
{
	public static function setUpBeforeClass(): void {
		$DB_PARAMS = [
			'database'=>'127.0.0.1:E:\dbf30\test.fdb',
			'username'=>'SYSDBA',
			'password'=>'masterkey',
		];

		SQL::$lex = 'fbird';
		self::$db = (new IBase)->connect_params($DB_PARAMS);
		//self::$db->set_default_fetch_function('fetch_object');
	}
}
