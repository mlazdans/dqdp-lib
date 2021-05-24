<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Generator extends FirebirdObject
{
	// function __construct(Database $db, $name){
	// 	$this->type = FirebirdObject::TYPE_GENERATOR;
	// 	parent::__construct($db, $name);
	// }

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$GENERATORS')
		->Where('RDB$SYSTEM_FLAG = 0');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$GENERATOR_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		return "CREATE GENERATOR $this";
	}

	function getValue(){
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query("SELECT GEN_ID($this, 0) AS GENERATOR_VALUE FROM RDB\$DATABASE");
		$r = $conn->fetch_object($q);

		return $r->GENERATOR_VALUE;
	}
}
