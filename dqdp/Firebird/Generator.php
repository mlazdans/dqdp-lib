<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class Generator extends FirebirdObject
{
	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_GENERATOR;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'g.RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('g.RDB$GENERATOR_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			g.*
		FROM
			RDB$GENERATORS g
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(){
		return "CREATE GENERATOR $this";
	}

	function getValue(){
		$sql = "SELECT GEN_ID($this, 0) AS GENERATOR_VALUE FROM RDB\$DATABASE";
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		$r = $conn->fetch_object($q);

		return $r->GENERATOR_VALUE;
	}

}
