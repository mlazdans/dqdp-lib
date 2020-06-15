<?php

use dqdp\FS;
use PHPUnit\Framework\TestCase;

abstract class FSTest extends TestCase
{
	protected static $db;

	function delete_all($tr = null){
		if(is_null($tr)){
			$tr = self::$db->trans();
			$FS = (new FS())->set_trans($tr);
			$this->assertTrue($FS->rmtree("/"));
			$this->assertTrue($tr->commit());
		} else {
			$FS = (new FS())->set_trans($tr);
			$this->assertTrue($FS->rmtree("/"));
		}
	}

	public function test0() {
		$this->delete_all();
	}

	public function test1() {
		$this->delete_all();

		$tr = self::$db->trans();
		$FS = (new FS())->set_trans($tr);
		$this->assertTrue($FS->mkdir("/") !== false);
		$this->assertTrue($FS->mkdir("/b") !== false);
		$this->assertTrue($FS->mkdir("/c") !== false);
		$this->assertTrue($FS->mkdir("/d/d") !== false);
		$this->assertEquals($FS->scandir("/"), ['b', 'c', 'd']);
		$this->assertTrue($tr->commit());
	}

	public function test2() {
		$this->delete_all();

		$tr = self::$db->trans();
		$FS = (new FS())->set_trans($tr);
		$this->assertTrue($FS->mkdir("/") !== false);
		$this->assertTrue($FS->mkdir("/b") !== false);
		$this->assertTrue($FS->mkdir("/c") !== false);
		$this->assertTrue($FS->mkdir("/d/d") !== false);
		$this->assertEquals($FS->scandir("/"), ['b', 'c', 'd']);
		$this->assertTrue($tr->rollback());

		$this->assertEquals($FS->scandir("/"), []);
	}

	public function test3() {
		$this->delete_all();

		$tr = self::$db->trans();
		$FS = (new FS())->set_trans($tr);
		$this->assertTrue($FS->write("/test", "some data") !== false);
		$this->assertTrue($tr->commit());
	}

}
