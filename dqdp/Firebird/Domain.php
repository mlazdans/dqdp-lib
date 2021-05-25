<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Domain extends Field
{
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

	function ddl(): string {
		return "CREATE DOMAIN $this AS ".parent::ddl();
	}
}
