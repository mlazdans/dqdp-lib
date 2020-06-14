<?php

use dqdp\FS;
use PHPUnit\Framework\TestCase;

abstract class FSTest extends TestCase
{
	protected static $db;

	public function test1() {
		//self::$db->set_default_fetch_function('fetch_object');
		$tr = self::$db->trans();
		$FS = (new FS())->set_trans($tr);
		$FS->rmtree("/");
		$this->assertTrue($FS->mkdir("/") !== false);
		$this->assertTrue($FS->mkdir("/b") !== false);
		$tr->commit();
	}

}
