<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Domain extends FirebirdObject
{
	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_DOMAIN;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql = (new Select())
		->From('RDB$FIELDS f')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = f.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->Where('f.RDB$SYSTEM_FLAG = 0')
		->Where(['f.RDB$FIELD_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(){
		$ddl = array();
		$MT = $this->getMetadata();

		$ddl[] = "CREATE DOMAIN $this AS ".Field::ddl($MT);

		/*
		Move to IbaseField::ddl()?
		if($MT->DEFAULT_SOURCE){
			$ddl[] = $MT->DEFAULT_SOURCE;
		}

		if($MT->NULL_FLAG){
			$ddl[] = "NOT NULL";
		}
		*/

		if($MT->VALIDATION_SOURCE){
			$ddl[] = $MT->VALIDATION_SOURCE;
		}

		return join("\n", $ddl);
	}
}
