<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Index extends FirebirdType
{
	const TYPE_INDEX    = 0;
	const TYPE_FK       = 1;
	const TYPE_PK       = 2;
	const TYPE_UNIQUE   = 3;

	const INDEX_TYPE_ASC  = 0;
	const INDEX_TYPE_DESC = 1;

	static function getSQL(): Select {
		return (new Select('i.*'))
		->From('RDB$INDICES i')
		->Where('i.RDB$SYSTEM_FLAG = 0');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['i.RDB$INDEX_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function getSegments(): array {
		$sql = (new Select())->From('RDB$INDEX_SEGMENTS')
		->Where(['RDB$INDEX_NAME = ?', $this->name])
		->OrderBy('RDB$FIELD_POSITION')
		;

		foreach($this->getList($sql) as $r){
			$list[] = $r->FIELD_NAME;
		}

		return $list??[];
	}

	function ddlParts(): array {
		trigger_error("Do not call directly. Use RelationIndex");
		return [];
	}

	function ddl($PARTS = null): string {
		trigger_error("Do not call directly. Use RelationIndex");
		return "";
	}
}
