<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Index extends FirebirdObject
{
	const TYPE_ASC   = 0;
	const TYPE_DESC  = 1;

	// const TYPE_INDEX    = 0;
	// const TYPE_FK       = 1;
	// const TYPE_PK       = 2;
	// const TYPE_UNIQUE   = 3;

	static function getSQL(): Select {
		return (new Select())->From('RDB$INDICES AS indices')->Where('indices.RDB$SYSTEM_FLAG = 0');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['indices.RDB$INDEX_NAME = ?', $this->name]);
	}

	function getSegments(): array {
		$sql = (new Select())->From('RDB$INDEX_SEGMENTS')->Where(['RDB$INDEX_NAME = ?', $this->name])->OrderBy('RDB$FIELD_POSITION');

		foreach($this->getList($sql) as $r){
			$list[] = $r->FIELD_NAME;
		}

		return $list??[];
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$PARTS['indexname'] = $MD->INDEX_NAME;
		$PARTS['tablename'] = $MD->RELATION_NAME;

		if($MD->UNIQUE_FLAG){
			$PARTS['unique'] = "UNIQUE";
		}

		if($MD->INDEX_TYPE == RelationIndex::TYPE_DESC){
			$PARTS['type'] = "DESCENDING";
		} else {
			$PARTS['type'] = "ASCENDING";
		}

		if($MD->SEGMENT_COUNT){
			$PARTS['col_list'] = $this->getSegments();
		} else {
			$PARTS['expression'] = $MD->EXPRESSION_SOURCE;
		}

		$PARTS['active'] = $MD->INDEX_INACTIVE ? "INACTIVE" : "ACTIVE";

		return $PARTS;
	}
}
