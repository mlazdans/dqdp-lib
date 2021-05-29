<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE {SEQUENCE | GENERATOR} seq_name
//   [START WITH start_value]
//   [INCREMENT [BY] increment]

class Generator extends FirebirdObject implements DDL
{
	static function getSQL(): Select {
		return (new Select())->From('RDB$GENERATORS AS generators')->Where('generators.RDB$SYSTEM_FLAG = 0');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['generators.RDB$GENERATOR_NAME = ?', $this->name]);
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$PARTS['seq_name'] = "$this";
		$PARTS['start_value'] = $MD->INITIAL_VALUE;
		$PARTS['increment'] = $MD->GENERATOR_INCREMENT;

		return $PARTS;
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		$ddl = ["$PARTS[seq_name]"];

		if($PARTS['start_value']){
			$ddl[] = "START WITH $PARTS[start_value]";
		}

		if($PARTS['increment'] > 1){
			$ddl[] = "INCREMENT BY $PARTS[increment]";
		}

		return join(" ", $ddl);
	}

	// function getValue(){
	// 	$conn = $this->getDb()->getConnection();
	// 	$q = $conn->Query("SELECT GEN_ID($this, 0) AS GENERATOR_VALUE FROM RDB\$DATABASE");
	// 	$r = $conn->fetch_object($q);

	// 	return $r->GENERATOR_VALUE;
	// }
}
