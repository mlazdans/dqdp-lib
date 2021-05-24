<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Domain extends Field
{
	// function __construct(Database $db, $name){
	// 	$this->type = FirebirdObject::TYPE_DOMAIN;
	// 	parent::__construct($db, $name);
	// }

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$FIELDS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where('RDB$FIELD_NAME NOT LIKE \'RDB$%\'')
		->OrderBy('RDB$FIELD_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$FIELD_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	// function loadMetadata(){
	// 	$sql = (new Select())
	// 	->From('RDB$FIELDS f')
	// 	->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = f.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
	// 	->Where('f.RDB$SYSTEM_FLAG = 0')
	// 	->Where(['f.RDB$FIELD_NAME = ?', $this->name])
	// 	;

	// 	return parent::loadMetadataBySQL($sql);
	// }

	function ddl(): string {
		return "CREATE DOMAIN $this AS ".parent::ddl();
	}
}
